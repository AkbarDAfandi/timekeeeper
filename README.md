# School-Timekeeper: Sistem Bel & Pengumuman Otomatis Sekolah

**School-Timekeeper** adalah aplikasi *web-based* untuk mengelola jadwal bel sekolah, pengumuman otomatis, dan siaran audio *real-time* melalui *player nodes*. Proyek ini dikembangkan sebagai portofolio *Full-Stack Development* yang mendemonstrasikan implementasi arsitektur *multi-tenant*, *real-time broadcasting*, dan *role-based access control*.

![Project Status](https://img.shields.io/badge/Status-In%20Development-yellow)
![Tech Stack](https://img.shields.io/badge/Backend-PHP%20%2F%20Laravel-red)
![Filament](https://img.shields.io/badge/Admin-Filament%205-purple)
![Real-time](https://img.shields.io/badge/Real--time-Laravel%20Reverb-blue)

## ⚠️ Disclaimer

Proyek ini sedang dalam tahap **pengembangan aktif (In Development)**. Beberapa fitur mungkin belum sepenuhnya stabil atau masih dalam penyempurnaan.

> **Catatan Pengembangan:**
> - Fitur *multi-database tenant* sudah terimplementasi namun mungkin memerlukan penyesuaian untuk production.
> - *Broadcasting* event memerlukan Laravel Reverb yang sedang berjalan dan terkonfigurasi dengan benar.
> - Audio playback pada *player node* bergantung pada kebijakan *autoplay* browser — gunakan tombol "Initialize Audio Engine" untuk mengaktifkan audio.
> - Tes otomatis masih terbatas; kontribusi sangat diterima.


---

## 🚀 Fitur Utama (Technical Features)

### 1. Manajemen Jadwal Bel (Schedule Management)
- **FullCalendar Integration** — Antarmuka kalender interaktif dengan *drag-and-drop* untuk membuat, mengedit, dan menghapus jadwal.
- **Recurring Schedules** — Jadwal berulang berdasarkan hari dalam seminggu (Senin—Jumat, dll).
- **Real-time Sync** — Setiap perubahan jadwal langsung disiarkan ke seluruh *player node* melalui WebSocket.

### 2. Siaran Audio Real-Time (Real-time Broadcasting)
- **Laravel Reverb** — WebSocket server *first-party* Laravel untuk komunikasi *real-time* antara panel admin dan *player nodes*.
- **Scheduled Playback** — *Player node* otomatis memutar audio sesuai jadwal harian (pengecekan setiap 15 detik).
- **Ad-hoc Announcement** — Admin dapat menyiarkan pengumuman dadakan secara instan ke seluruh perangkat pemutar.

### 3. Manajemen Preset Audio (Audio Preset Management)
- **Global Presets** (Superadmin) — Koleksi audio sistem seperti bel, jingle, dan chime yang dapat digunakan seluruh tenant.
- **Tenant Presets** (Admin) — Template pengumuman bilingual (Indonesia & Inggris) dengan *placeholder* `{time}` dan `{title}`.

### 4. Multi-Tenant & Role-Based Access (RBAC)
- **Multi-Tenant Isolation** — Setiap sekolah adalah tenant terpisah dengan database sendiri.
- **4 Role Levels** — Superadmin, Admin, Operator, dan Player dengan hak akses bertingkat.
- **Player Authentication** — Autentikasi terpisah untuk perangkat pemutar audio (*player nodes*).

### 5. Admin Panel Modern
- **Filament 5** — Panel administrasi modern dan responsif.
- **Dashboard Kalender** — Tampilan jadwal hari ini dengan indikator waktu sekarang (*now-indicator*).
- **Instant Broadcast Page** — Halaman khusus untuk menyiarkan pengumuman langsung.

## 🛠️ Teknologi yang Digunakan (Tech Stack)

| Layer | Teknologi |
|-------|-----------|
| **Bahasa** | PHP 8.3 |
| **Framework** | Laravel 13 |
| **Admin Panel** | Filament 5 + FullCalendar |
| **Real-time** | Laravel Reverb (WebSocket) |
| **Database** | MySQL / MariaDB (multi-database per tenant) |
| **Frontend** | Tailwind CSS 4, Vite 8 |
| **WebSocket Client** | Laravel Echo + Pusher JS |
| **Queue** | Database (Laravel Queue) |
| **Testing** | Pest PHP |
| **Tools** | Git, Composer, npm |

## 📋 Cara Menjalankan (How to Run)

### Prasyarat
- PHP ≥ 8.3 dengan ekstensi: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL / MariaDB
- Composer & Node.js (≥ 18)
- Git

### Langkah 1: Clone & Install Dependencies
```bash
git clone <repository-url>
cd school-timekeeper
composer setup
```

Perintah `composer setup` otomatis menjalankan:
- `composer install`
- Menyalin `.env.example` → `.env`
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install`
- `npm run build`

### Langkah 2: Konfigurasi Environment
Sesuaikan file `.env` sesuai environment kamu:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_timekeeper
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
```

### Langkah 3: Seed Database
```bash
php artisan db:seed
```

Akun default setelah seeding:
| Role | Email | Password |
|------|-------|----------|
| Superadmin | `superadmin@app.com` | `password` |
| Admin | `admin@smkn1lmj.sch.id` | `password` |
| Operator | `operator@smkn1lmj.sch.id` | `password` |
| Player | `player@smkn1lmj.sch.id` | `password` |

### Langkah 4: Menjalankan Aplikasi (Development)
```bash
composer dev
```

Perintah ini menjalankan 4 proses secara bersamaan:
- **Server** — `php artisan serve` (http://localhost:8000)
- **Queue** — `php artisan queue:listen` (pemrosesan job)
- **Logs** — `php artisan pail` (log tailing)
- **Vite** — `npm run dev` (hot module replacement)

Atau jalankan secara manual di terminal terpisah:
```bash
# Terminal 1: Laravel dev server
php artisan serve

# Terminal 2: Reverb WebSocket server
php artisan reverb:start

# Terminal 3: Queue worker
php artisan queue:listen

# Terminal 4: Vite dev server
npm run dev
```

### Langkah 5: Akses Aplikasi
| Halaman | URL | Keterangan |
|---------|-----|------------|
| Admin Panel | `http://localhost:8000/admin` | Panel administrasi (Filament) |
| Player Login | `http://localhost:8000/source-login` | Login perangkat pemutar |
| Player Display | `http://localhost:8000/source-display` | Dashboard pemutar audio |

### Production Deployment
Untuk production, jalankan:
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan reverb:start --no-interaction &
php artisan queue:work --daemon &
```

