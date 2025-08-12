@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/index.css')}}">
@endsection

@section('content')
<div class="admin-attendances">
    <div class="admin-attendances__title">{{ $currentDate->format('Y年m月d日') }}の勤務</div>

    <div class="admin-attendances__date-selector">
        <a href="{{ route('admin.attendances.index', ['date' => $prevDate->toDateString()]) }}" class="admin-attendances__arrow">
            &larr; 前日
        </a>
        <span class="admin-attendances__month-label">
            <i class="fa fa-calendar"></i>
            <strong>{{ $currentDate->format('Y/m/d') }}</strong>
        </span>
        <a href="{{ route('admin.attendances.index', ['date' => $nextDate->toDateString()]) }}" class="admin-attendances__arrow">
            翌日 &rarr;
        </a>
    </div>

    <table class="admin-attendances__table">
        <thead class="admin-attendances__table-head">
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody class="admin-attendances__table-body">
            @forelse ($attendances as $attendance)
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

                    $breaks = $approved ? $approved->application_break_times : $attendance->breaks;

                    $totalBreakMinutes = $breaks->sum(function ($break) {
                        $start = \Carbon\Carbon::parse($break->break_in_time ?? $break->start_time);
                        $end = \Carbon\Carbon::parse($break->break_out_time ?? $break->end_time);
                        return $end->diffInMinutes($start);
                    });

                    $breakDuration = $totalBreakMinutes > 0
                        ? floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '-';

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
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $clockIn }}</td>
                    <td>{{ $clockOut }}</td>
                    <td>{{ $breakDuration }}</td>
                    <td>{{ $workingHours }}</td>
                    <td><a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">詳細</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">該当する勤怠データがありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection