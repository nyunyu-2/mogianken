@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff.css')}}">
@endsection

@section('content')
<div class="staff-list">
    <div class="staff-list__title">スタッフ一覧</div>

    <table class="staff-list__table">
        <thead class="staff-list__table-head">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤務</th>
            </tr>
        </thead>
        <tbody class="staff-list__table-body">
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('admin.staff.attendance.show', ['id' => $user->id]) }}">詳細</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection