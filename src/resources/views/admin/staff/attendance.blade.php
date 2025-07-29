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
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}</td>
                    <td>{{ $attendance->formatted_clock_in_time }}</td>
                    <td>{{ $attendance->formatted_clock_out_time }}</td>
                    <td>{{ $attendance->break_duration ?? '-' }}</td>
                    <td>{{ $attendance->working_hours ?? '-' }}</td>
                    <td><a href="">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection