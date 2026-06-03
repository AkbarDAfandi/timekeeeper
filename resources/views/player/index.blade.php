<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="tenant-id" content="{{ auth()->user()->tenant_id }}">
    <title>Audio Player Node</title>
    <style>
        :root {
            --primary-50: #fffbeb;
            --primary-100: #fef3c7;
            --primary-200: #fde68a;
            --primary-300: #fcd34d;
            --primary-400: #fbbf24;
            --primary-500: #f59e0b;
            --primary-600: #d97706;
            --primary-700: #b45309;
            --primary-800: #92400e;
            --primary-900: #78350f;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --gray-950: #030712;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: var(--gray-950);
            color: var(--gray-100);
            padding: 32px;
            min-height: 100vh;
        }

        .header {
            margin-bottom: 32px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.025em;
        }

        .header p {
            font-size: 13px;
            color: var(--gray-400);
            margin-top: 4px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .clock-card {
            grid-row: 1 / 3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .clock-card .card-label {
            font-size: 13px;
            margin-bottom: 12px;
        }

        .clock-card .card-value {
            font-size: 120px;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .side-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .init-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background: var(--primary-500);
            color: var(--gray-950);
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.15s;
            margin-bottom: 24px;
        }

        .init-btn:hover {
            background: var(--primary-400);
        }

        .card {
            background: var(--gray-900);
            border: 1px solid var(--gray-800);
            border-radius: 12px;
            padding: 20px;
        }

        .card-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .card-value {
            font-size: 26px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.025em;
        }

        .card-value.muted {
            color: var(--gray-500);
        }

        .card-value.accent {
            color: var(--primary-400);
        }

        .status {
            font-size: 12px;
            color: var(--gray-400);
            padding: 10px 14px;
            background: var(--gray-900);
            border: 1px solid var(--gray-800);
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .status span {
            color: var(--primary-400);
            font-weight: 500;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            padding: 10px 16px;
            border-bottom: 1px solid var(--gray-800);
        }

        .schedule-table td {
            font-size: 13px;
            color: var(--gray-300);
            padding: 10px 16px;
            border-bottom: 1px solid var(--gray-800 / 0.5);
        }

        .schedule-table tr.active td {
            color: var(--primary-400);
            background: var(--gray-900);
        }

        .schedule-table tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-active {
            background: var(--primary-500) / 0.15;
            color: var(--primary-400);
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Audio Player Node</h1>
        <p>Scheduled announcement playback engine</p>
    </div>

    <div class="grid">
        <div class="card clock-card">
            <div class="card-label">Current Time</div>
            <div class="card-value" id="clock">--:--:--</div>
        </div>
        <div class="side-stack">
            <div class="card">
                <div class="card-label">Current Schedule</div>
                <div class="card-value muted" id="current-schedule">--</div>
            </div>
            <div class="card">
                <div class="card-label">Next Schedule</div>
                <div class="card-value muted" id="next-schedule">--</div>
            </div>
        </div>
    </div>

    <button class="init-btn" id="init-engine">Initialize Audio Engine</button>

    <div class="status" id="status">Engine status: <span>awaiting initialization</span></div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="schedule-body">
            </tbody>
        </table>
    </div>

    @vite(['resources/css/app.css', 'resources/js/player.js'])
</body>

</html>
