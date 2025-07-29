@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/edit.css')}}">
@endsection

@section('content')
<div class="admin-edit">
    <div class="admin-edit__title">勤怠詳細</div>
    <form class="attendance-detail__form" action="{{ route('admin.approval.approve') }}" method="POST">
        @csrf
        <input type="hidden" name="application_id" value="{{ $application->id }}">

        <div class="admin-edit__table">
            <table class="admin-edit__inner">
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">名前</th>
                    <td class="admin-edit__content">{{ $application->attendance->user->name }}</td>
                </tr>
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">日付</th>
                    <td class="admin-edit__content-date">
                        {{ \Carbon\Carbon::parse($application->attendance->date)->locale('ja')->isoFormat('YYYY年') }}
                        <span>{{ \Carbon\Carbon::parse($application->attendance->date)->locale('ja')->isoFormat('M月D日') }}
                    </td>
                </tr>
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">出勤・退勤</th>
                    <td class="admin-edit__content">
                        <span class="attendance-detail__text-display">
                            {{ $application->attendance->formatted_clock_in_time }}
                        </span>
                        <span class="line"> ～ </span>
                        <span class="attendance-detail__text-display">
                            {{ $application->attendance->formatted_clock_out_time }}
                        </span>
                    </td>
                </tr>
                @if ($application->application_break_times && $application->application_break_times->isNotEmpty())
                    @foreach ($application->application_break_times as $i => $break)
                        <tr class="admin-edit__row">
                            <th class="admin-edit__header">休憩{{ $i + 1 }}</th>
                            <td class="admin-edit__content">
                                <span class="attendance-detail__text-display">
                                    {{ \Carbon\Carbon::parse($break->break_in_time)->format('H:i') }}
                                </span>
                                <span class="line"> ～ </span>
                                <span class="attendance-detail__text-display">
                                    {{ \Carbon\Carbon::parse($break->break_out_time)->format('H:i') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="admin-edit__row">
                        <th class="admin-edit__header">休憩{{ $i + 1 }}</th>
                        <td colspan="2">休憩申請データがありません</td>
                    </tr>
                @endif
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">備考</th>
                    <td class="admin-edit__content">
                        <p class="attendance-detail__textarea-display">{{ $application->reason }}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="admin-edit__button">
            @if ($application->is_pending)
                <form action="{{ route('admin.approval.approve') }}" method="POST">
                    @csrf
                    <input type="hidden" name="application_id" value="{{ $application->id }}">
                    <button type="submit" class="btn btn-primary">承認</button>
                </form>
            @else
                <span class="btn btn-secondary disabled">承認済み</span>
            @endif
        </div>
    </form>
</div>
@endsection