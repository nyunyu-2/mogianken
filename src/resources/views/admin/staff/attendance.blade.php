@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-attendance.css')}}">
@endsection

@section('content')
<div class="staff-attendance">
    <div class="staff-attendance__title">{{ $user->name }}さんの出勤</div>

    <div class="staff-attendance__month-selector">
        <a href="{{ route('admin.staff.attendance.show', ['id' => $user->id, 'month' => $prevMonth]) }}" class="staff-attendance__arrow">&larr; 前月</a>
            <span class="staff-attendance__month-label">
                <i class="fa fa-calendar"></i>
                <strong>{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</strong>
            </span>
        <a href="{{ route('admin.staff.attendance.show', ['id' => $user->id, 'month' => $nextMonth]) }}" class="staff-attendance__arrow">翌月 &rarr;</a>
    </div>

    <table class="staff-attendance__table">
        <thead class="staff-attendance__table-head">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody class="staff-attendance__table-body">
        @forelse ($attendances as $attendance)
            @php
                $approved = $attendance->applications->where('status', '承認済み')->sortByDesc('updated_at')->first();

                $pending = $attendance->applications->where('status', '承認待ち')->sortByDesc('updated_at')->first();

                $application = $pending ?? $approved;

                $clockIn = $application && $application->clock_in_time
                    ? \Carbon\Carbon::parse($application->clock_in_time)->format('H:i')
                    : ($attendance->clock_in_time
                        ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
                        : '-');

                $clockOut = $application && $application->clock_out_time
                    ? \Carbon\Carbon::parse($application->clock_out_time)->format('H:i')
                    : ($attendance->clock_out_time
                        ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
                        : '-');

                $breaks = $application ? $application->application_break_times : $attendance->breaks;

                $totalBreakMinutes = $breaks->sum(function ($break) {
                    $start = \Carbon\Carbon::parse($break->break_in_time ?? $break->start_time);
                    $end = \Carbon\Carbon::parse($break->break_out_time ?? $break->end_time);
                    return $end->diffInMinutes($start);
                });

                $breakDuration = $totalBreakMinutes > 0
                    ? floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT)
                    : '-';

                if ($application && $application->clock_in_time && $application->clock_out_time) {
                    $start = \Carbon\Carbon::parse($application->clock_in_time);
                    $end = \Carbon\Carbon::parse($application->clock_out_time);
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
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}</td>
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