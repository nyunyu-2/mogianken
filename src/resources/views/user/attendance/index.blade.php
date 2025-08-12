@extends('layouts/user/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/index.css')}}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__title">勤怠一覧</div>

    <div class="attendance-list__month-selector">
        <a href="{{ route('user.attendance.index', ['month' => $prevMonth]) }}" class="attendance-list__arrow">&larr; 前月</a>
            <span class="attendance-list__month-label">
                <i class="fa fa-calendar"></i>
                <strong>{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</strong>
            </span>
        <a href="{{ route('user.attendance.index', ['month' => $nextMonth]) }}" class="attendance-list__arrow">翌月 &rarr;</a>
    </div>

    <table class="attendance-list__table">
        <thead class="attendance-list__table-head">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody class="attendance-list__table-body">
            @foreach ($attendances as $attendance)
                @php
                    $approved = $attendance->applications->where('status', '承認済み')->sortByDesc('updated_at')->first();

                    $clockIn = $approved && $approved->clock_in_time
                        ? \Carbon\Carbon::parse($approved->clock_in_time)->format('H:i')
                        : ($attendance->clock_in_time
                            ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
                            : '-');

                    $clockOut = $approved && $approved->clock_out_time
                        ? \Carbon\Carbon::parse($approved->clock_out_time)->format('H:i')
                        : ($attendance->clock_out_time
                            ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
                            : '-');

                    // 承認済み申請の休憩時間（分）を取得
                    $breaks = $approved ? $approved->application_break_times : $attendance->breaks;

                    $totalBreakMinutes = $breaks->sum(function ($break) {
                        $start = \Carbon\Carbon::parse($break->break_in_time ?? $break->start_time);
                        $end = \Carbon\Carbon::parse($break->break_out_time ?? $break->end_time);
                        return $end->diffInMinutes($start);
                    });

                    $breakDuration = $totalBreakMinutes > 0
                        ? floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '-';

                    // 勤務時間計算（承認済み申請の時間を使用）
                    if ($approved && $approved->clock_in_time && $approved->clock_out_time) {
                        $start = \Carbon\Carbon::parse($approved->clock_in_time);
                        $end = \Carbon\Carbon::parse($approved->clock_out_time);
                        $workDurationMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
                    } elseif ($attendance->clock_in_time && $attendance->clock_out_time) {
                        $start = \Carbon\Carbon::parse($attendance->clock_in_time);
                        $end = \Carbon\Carbon::parse($attendance->clock_out_time);
                        $workDurationMinutes = $end->diffInMinutes($start) - $totalBreakMinutes;
                    } else {
                        $workDurationMinutes = null;
                    }

                    $workingHours = $workDurationMinutes !== null
                        ? floor($workDurationMinutes / 60) . ':' . str_pad($workDurationMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '-';
                @endphp

                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                    <td>{{ $clockIn }}</td>
                    <td>{{ $clockOut }}</td>
                    <td>{{ $breakDuration }}</td>
                    <td>{{ $workingHours }}</td>
                    <td><a href="{{ route('user.attendance.show',['id' => $attendance->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection