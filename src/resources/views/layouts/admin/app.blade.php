<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Management Admin</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/admin-common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" style="height: 36px;">
            </a>
            <div class="header__nav">
                <a href="{{ url('/admin/attendances') }}" class="header__work-button">勤怠一覧</a>
                <a href="{{ url('/admin/staff/list') }}" class="header__list-button">スタッフ一覧</a>
                <a href="{{ url('/admin/requests') }}" class="header__application-button">申請一覧</a>
                <form>
                    @csrf
                    <a type="submit" class="header__logout-button">ログアウト</a>
                </form>
            </div>
        </div>
    </header>
    <div class="content">
        @yield('content')
    </div>
</body>