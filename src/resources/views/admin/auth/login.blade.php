<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flea Market</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/admin-login.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" style="height: 36px;">
            </a>
        </div>
    </header>
    <main>
        <div class="login-form__content">
            <div class="login-form__heading">
                <h2 class="login-form__title">ログイン</h2>
            </div>

            <form class="login-form__form" method="POST" action="{{ route('admin.authenticate') }}">
                @csrf
                <div class="login-form__group">
                    <div class="login-form__label-wrapper">
                        <span class="login-form__label">メールアドレス</span>
                    </div>
                    <div class="login-form__field">
                        <div class="login-form__field--text">
                            <input type="email" name="email" value="{{ old('email') }}" />
                        </div>
                    </div>
                </div>
                <div class="login-form__group">
                    <div class="login-form__label-wrapper">
                        <span class="login-form__label">パスワード</span>
                    </div>
                    <div class="login-form__field">
                        <div class="login-form__field--text">
                            <input type="password" name="password" />
                        </div>
                    </div>
                </div>

                <div class="login-form__button-wrapper">
                    <button class="login-form__button-login" type="submit">ログインする</button>
                </div>
            </form>
        </div>
    </main>
</body>