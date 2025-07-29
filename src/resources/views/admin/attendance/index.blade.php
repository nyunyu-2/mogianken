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
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
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