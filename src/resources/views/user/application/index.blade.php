@extends('layouts/user/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/application.css')}}">
@endsection

@section('content')
<div class="application">
    <div class="application__title">申請一覧</div>

    <input type="radio" id="tab-pending" name="tab" class="tab-radio" checked>
    <input type="radio" id="tab-approved" name="tab" class="tab-radio">

    <div class="application__tabs">
        <label for="tab-pending">承認待ち</label>
        <label for="tab-approved">承認済み</label>
    </div>

    <div class="application__content">
        <div class="application__tab-content" id="content-pending">
            <table class="application__table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingApplications as $application)
                        <tr>
                            <td>{{ $application->status }}</td>
                            <td>{{ $application->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($application->attendance->date)->format('Y/m/d') }}</td>
                            <td>{{ $application->reason }}</td>
                            <td>{{ $application->created_at->format('Y/m/d') }}</td>
                            <td><a href="{{ route('user.attendance.show', ['id' => $application->attendance_id]) }}">詳細</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">承認待ちの申請はありません。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="application__tab-content" id="content-approved">
            <table class="application__table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($approvedApplications as $application)
                        <tr>
                            <td>{{ $application->status }}</td>
                            <td>{{ $application->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($application->attendance->date)->format('Y/m/d') }}</td>
                            <td>{{ $application->reason }}</td>
                            <td>{{ $application->created_at->format('Y/m/d') }}</td>
                            <td><a href="{{ route('user.attendance.show', ['id' => $application->attendance_id]) }}">詳細</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">承認済みの申請はありません。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
