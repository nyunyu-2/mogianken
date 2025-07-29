@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/.css')}}">
@endsection

@section('content')
<div class="admin-edit">
    <div class="admin-edit__title">勤怠詳細</div>
    <form>
        <div class="admin-edit__table">
            <table class="admin-edit__inner">
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">名前</th>
                    <td class="admin-edit__content"></td>
                </tr>
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">日付</th>
                    <td class="admin-edit__content"></td>
                </tr>
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">出勤・退勤</th>
                    <td class="admin-edit__content"></td>
                </tr>
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">休憩</th>
                    <td class="admin-edit__content"></td>
                </tr>
                <tr class="admin-edit__row">
                    <th class="admin-edit__header">備考</th>
                    <td class="admin-edit__content"></td>
                </tr>
            </table>
        </div>
    </form>
</div>
@endsection