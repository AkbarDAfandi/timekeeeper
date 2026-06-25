let schedules = [];
let playedToday = new Set();
let audioUnlocked = false;
let activeAudio = null;

function getNow() {
    const now = new Date();
    return {
        date: now,
        hhmm: `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`,
        hhmmss: `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`,
    };
}

function normalizeTime(t) {
    const parts = t.split(':');
    return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
}

function timeToMinutes(t) {
    const [h, m] = normalizeTime(t).split(':').map(Number);
    return h * 60 + m;
}

function unlockAudio() {
    const silent = new Audio('data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAQB8AAIA+AAACABAAZGF0YQAAAAA=');
    silent.volume = 0;
    silent.play().then(() => {
        audioUnlocked = true;
        const statusEl = document.getElementById('status');
        if (statusEl) statusEl.innerHTML = 'Engine status: <span>standby</span>';
        console.log('Audio engine unlocked');
    }).catch(err => {
        console.error('Audio unlock failed:', err);
    });
}

function playAudio(url) {
    if (activeAudio) {
        activeAudio.pause();
        activeAudio.src = '';
    }

    const audio = new Audio(url);
    activeAudio = audio;

    if (!audioUnlocked) {
        console.warn('Audio not unlocked, attempting unlock on play');
        unlockAudio();
    }

    audio.play().then(() => {
        console.log('Playback started:', url);
    }).catch(error => {
        console.error('Playback failed:', error);
        if (error.name === 'NotAllowedError') {
            console.warn('Blocked by browser autoplay policy. Click the page to unlock audio.');
        }
    });

    audio.onended = () => {
        activeAudio = null;
        const statusEl = document.getElementById('status');
        if (statusEl) statusEl.innerHTML = 'Engine status: <span>standby</span>';
    };

    audio.onerror = (e) => {
        console.error('Audio error:', e);
        activeAudio = null;
    };
}

function updateDisplay() {
    const clockEl = document.getElementById('clock');
    const currentScheduleEl = document.getElementById('current-schedule');
    const nextScheduleEl = document.getElementById('next-schedule');
    const tableBody = document.getElementById('schedule-body');

    const now = getNow();
    if (clockEl) clockEl.textContent = now.hhmmss;

    const sorted = [...schedules].sort((a, b) => timeToMinutes(a.time) - timeToMinutes(b.time));
    const currentMinutes = timeToMinutes(now.hhmm);

    let current = null;
    let next = null;

    for (const s of sorted) {
        const sMin = timeToMinutes(s.time);
        if (sMin === currentMinutes) {
            current = s;
        } else if (sMin > currentMinutes && !next) {
            next = s;
        }
    }

    if (currentScheduleEl) {
        currentScheduleEl.textContent = current ? normalizeTime(current.time) : 'None';
        currentScheduleEl.classList.toggle('muted', !current);
        currentScheduleEl.classList.toggle('accent', !!current);
    }

    if (nextScheduleEl) {
        nextScheduleEl.textContent = next ? normalizeTime(next.time) : 'None';
        nextScheduleEl.classList.toggle('muted', !next);
        nextScheduleEl.classList.toggle('accent', !!next);
    }

    if (tableBody) {
        tableBody.querySelectorAll('tr').forEach(tr => {
            const isActive = normalizeTime(tr.dataset.time) === now.hhmm;
            tr.classList.toggle('active', isActive);
            const badge = tr.querySelector('[data-badge]');
            if (badge) badge.style.display = isActive ? 'inline-block' : 'none';
        });
    }
}

async function fetchSchedules() {
    const tableBody = document.getElementById('schedule-body');
    try {
        const response = await fetch('/source-payload');
        if (!response.ok) throw new Error('Network response was not ok');
        schedules = await response.json();

        if (tableBody) {
            tableBody.innerHTML = '';
            schedules.forEach(schedule => {
                const tr = document.createElement('tr');
                tr.dataset.time = schedule.time;
                tr.innerHTML = `
                    <td>${normalizeTime(schedule.time)}</td>
                    <td><span class="badge badge-active" style="display:none" data-badge="${schedule.id}">Playing</span></td>
                `;
                tableBody.appendChild(tr);
            });
        }
        updateDisplay();
    } catch (error) {
        console.error('Failed to fetch schedules:', error);
    }
}

function checkAndPlay() {
    const statusEl = document.getElementById('status');
    const now = getNow();

    schedules.forEach(schedule => {
        if (normalizeTime(schedule.time) === now.hhmm && !playedToday.has(schedule.id)) {
            playedToday.add(schedule.id);
            if (statusEl) statusEl.innerHTML = `Engine status: <span>playing ${normalizeTime(schedule.time)}</span>`;
            playAudio(schedule.url);
        }
    });

    updateDisplay();
}

document.addEventListener('DOMContentLoaded', () => {
    fetchSchedules();
    setInterval(checkAndPlay, 15000);
    setInterval(updateDisplay, 1000);

    const now = new Date();
    const msUntilMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1) - now;
    setTimeout(() => { playedToday.clear(); }, msUntilMidnight);

    document.addEventListener('click', () => {
        if (!audioUnlocked) unlockAudio();
    }, { once: true });

    const initBtn = document.getElementById('init-engine');
    if (initBtn) {
        initBtn.addEventListener('click', () => {
            unlockAudio();
            initBtn.textContent = 'Engine Initialized';
            initBtn.disabled = true;
            initBtn.style.opacity = '0.5';
        });
    }

    const tenantId = document.querySelector('meta[name="tenant-id"]')?.content;
    if (!tenantId) {
        console.error('Tenant ID not found');
        return;
    }

    import('laravel-echo').then(({ default: Echo }) => {
        return import('pusher-js').then(({ default: Pusher }) => {
            window.Pusher = Pusher;

            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST,
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
                wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
            });

            window.Echo.private(`tenant.${tenantId}`)
                .listen('AdhocAudioDispatched', (e) => {
                    const statusEl = document.getElementById('status');
                    if (statusEl) statusEl.innerHTML = 'Engine status: <span>broadcast override</span>';
                    playAudio(e.audioUrl);
                })
                .listen('SchedulesUpdated', () => {
                    console.log('Schedules updated, refreshing payload...');
                    fetchSchedules();
                });
        });
    }).catch(error => {
        console.error('Failed to initialize Echo:', error);
    });
});
