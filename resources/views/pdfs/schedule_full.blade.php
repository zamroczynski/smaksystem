<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Pracy - {{ $schedule['name'] }} - {{ $view_type }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; font-size: 10px; }
        h1, h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .day-header { background-color: #e0e0e0; font-weight: bold; }
        .sunday { background-color: #ffe0e0; }
        .saturday { background-color: #ffffcc; }
        .holiday { background-color: #ffe0e0; } 
        .user-name { font-size: 8px; line-height: 1.2; }
        .highlight-user { font-weight: bold; color: blue; }
        .week-title {
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>Grafik Pracy</h1>
    <h2>{{ $schedule['name'] }}</h2>
    <p style="text-align: center;">Typ widoku: {{ $view_type }}</p>

    @php
        // Funkcja do dzielenia tablicy na kawałki (chunks)
        function chunkArray($array, $chunkSize) {
            $chunks = [];
            for ($i = 0; $i < count($array); $i += $chunkSize) {
                $chunks[] = array_slice($array, $i, $chunkSize);
            }
            return $chunks;
        }

        $monthDayChunks = chunkArray($monthDays, 7);
    @endphp

    @foreach ($monthDayChunks as $chunkIndex => $dayChunk)
        <div class="week-title">Tydzień {{ $chunkIndex + 1 }}</div>
        <table>
            <thead>
                <tr>
                    <th>Zmiana</th> @foreach ($dayChunk as $day)
                        <th class="{{ $day['is_sunday'] ? 'sunday' : '' }} {{ $day['is_saturday'] ? 'saturday' : '' }} {{ $day['is_holiday'] ? 'holiday' : '' }}">
                            {{ $day['day_number'] }}
                        </th>
                    @endforeach
                </tr>
                <tr>
                    <th style="font-size: 8px; font-weight: normal; color: #555;">Dzień Tygodnia</th> @foreach ($dayChunk as $day)
                        <th style="font-size: 8px; font-weight: normal;"
                            class="{{ $day['is_sunday'] ? 'sunday' : '' }} {{ $day['is_saturday'] ? 'saturday' : '' }} {{ $day['is_holiday'] ? 'holiday' : '' }}">
                            {{ $day['day_name_short'] }}
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
                        @foreach ($dayChunk as $day)
                            <td class="{{ $day['is_sunday'] ? 'sunday' : '' }} {{ $day['is_saturday'] ? 'saturday' : '' }} {{ $day['is_holiday'] ? 'holiday' : '' }}">
                                @for ($i = 0; $i < $shiftTemplate['required_staff_count']; $i++)
                                    @php
                                        $assignmentKey = $shiftTemplate['id'] . '_' . $day['date'] . '_' . ($i + 1);
                                        $assignedUsers = $assignments[$assignmentKey] ?? []; // Zmieniono sposób dostępu do przypisań
                                    @endphp
                                    @if (!empty($assignedUsers))
                                        @foreach ($assignedUsers as $assignedUser)
                                            <div class="user-name {{ ($view_type === 'my' && isset($auth_user_id) && $assignedUser['user_id'] === $auth_user_id) ? 'highlight-user' : '' }}">
                                                {{ $assignedUser['user_name'] }}
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="user-name">-</div>
                                    @endif
                                @endfor
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>