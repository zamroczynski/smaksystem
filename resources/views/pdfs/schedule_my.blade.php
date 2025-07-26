<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Pracy - {{ $schedule['name'] }} - Mój</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; font-size: 10px; }
        h1, h2, h3 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .day-header { background-color: #e0e0e0; font-weight: bold; }
        .weekend { background-color: #ffe0e0; }
        .holiday { background-color: #ffe0e0; } /* Możesz dostosować, jeśli masz święta */
        .my-shift { background-color: #d4edda; font-weight: bold; } /* Podświetlenie mojej zmiany */
        .user-name { font-size: 8px; line-height: 1.2; }
    </style>
</head>
<body>
    <h1>Grafik Pracy</h1>
    <h2>{{ $schedule['name'] }}</h2>
    <h3>Miesiąc: {{ \Carbon\Carbon::parse($schedule['period_start_date'])->isoFormat('MMMM YYYY', 'Do MMMM YYYY', 'pl') }}</h3>
    <p style="text-align: center;">Typ widoku: {{ $view_type }}</p>

    <table>
        <thead>
            <tr>
                <th>Zmiana</th>
                @foreach ($monthDays as $day)
                    <th class="{{ $day['is_saturday'] || $day['is_sunday'] ? 'weekend' : '' }} {{ $day['is_holiday'] ? 'holiday' : '' }}">
                        {{ $day['day_number'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($shiftTemplates as $shiftTemplate)
                <tr>
                    <td>
                        {{ $shiftTemplate['name'] }}<br>
                        ({{ \Carbon\Carbon::parse($shiftTemplate['time_from'])->format('H:i') }} -
                         {{ \Carbon\Carbon::parse($shiftTemplate['time_to'])->format('H:i') }})
                    </td>
                    @foreach ($monthDays as $day)
                        <td class="{{ $day['is_saturday'] || $day['is_sunday'] ? 'weekend' : '' }} {{ $day['is_holiday'] ? 'holiday' : '' }}">
                            @php
                                $cellHasMyShift = false;
                                $assignedUsersForCell = [];
                                for ($i = 0; $i < $shiftTemplate['required_staff_count']; $i++) {
                                    $assignedForPos = $assignments[$shiftTemplate['id']][$day['date']][$i+1] ?? [];
                                    foreach ($assignedForPos as $assignedUser) {
                                        if (Auth::check() && $assignedUser['user_id'] == Auth::id()) {
                                            $cellHasMyShift = true;
                                        }
                                        $assignedUsersForCell[] = $assignedUser;
                                    }
                                }
                            @endphp
                            @if (!empty($assignedUsersForCell))
                                @foreach ($assignedUsersForCell as $assignedUser)
                                    <div class="user-name @if(Auth::check() && $assignedUser['user_id'] == Auth::id()) my-shift @endif">
                                        {{ $assignedUser['user_name'] }}
                                    </div>
                                @endforeach
                            @else
                                <div class="user-name">-</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>