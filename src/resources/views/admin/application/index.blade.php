@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/application.css')}}">
@endsection

@section('content')
<div class="admin-application">
    <div class="admin-application__title">申請一覧</div>
    <div class="admin-application__tabs">
        <input type="radio" id="tab-pending" name="tab" checked>
        <label for="tab-pending">承認待ち</label>

        <input type="radio" id="tab-approved" name="tab">
        <label for="tab-approved">承認済み</label>
    </div>
    <div class="admin-application__content">
        <table class="admin-application__table">
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
                @foreach ($applications as $application)
                <tr>
                    <td>{{ $application->status }}</td>
                    <td>{{ $application->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($application->attendance->date)->format('Y/m/d') }}</td>
                    <td>{{ $application->reason }}</td>
                    <td>{{ $application->created_at->format('Y/m/d') }}</td>
                    <td><a href="{{ route('admin.requests.edit', $application->id) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection