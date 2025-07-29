<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/user-common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" style="height: 36px;">
            </a>
            <div class="header__nav">
                <a href="{{ url('/attendance') }}" class="header__work-button">勤怠</a>
                <a href="{{ url('/attendance/list') }}" class="header__list-button">勤怠一覧</a>
                <a href="{{ url('/stamp_correction_request/list') }}" class="header__application-button">申請</a>
                @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="header__logout-button">ログアウト</button>
                </form>
                @endauth
            </div>
        </div>
    </header>
    <main class="content">
        <div class="content">
            @yield('content')
        </div>
    </main>
</body>