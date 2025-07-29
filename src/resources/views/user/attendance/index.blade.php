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
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                    <td>{{ $attendance->formatted_clock_in_time }}</td>
                    <td>{{ $attendance->formatted_clock_out_time }}</td>
                    <td>{{ $attendance->break_duration ?? '-' }}</td>
                    <td>{{ $attendance->working_hours ?? '-' }}</td>
                    <td><a href="{{ route('user.attendance.show',['id' => $attendance->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection