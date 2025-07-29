# 勤怠管理アプリ

## 使用環境

## 環境構築
1.git clone でリポジトリをクローン
2.docker-compose up -d --build
3.docker-compose exec php bash
4.composer install
5..env.example をコピーして .env を作成
6..env ファイルを必要に応じて修正
7.php artisan migrate
8.php artisan key:generate
9.php artisan db:seed
10.composer require laravel/fortify


## BLADEファイル
resources/views/
├── user/              ← 一般ユーザー用
│   ├── auth/          ← ログイン・登録系
│   │   ├── register.blade.php
│   │   └── login.blade.php
│   ├── attendance/
│   │   ├── create.blade.php     ← 勤怠登録画面
│   │   ├── index.blade.php      ← 勤怠一覧
│   │   └── show.blade.php       ← 勤怠詳細
│   ├── application/
│   │   └── index.blade.php      ← 申請一覧
│
├── admin/             ← 管理者用
│   ├── auth/
│   │   └── login.blade.php      ← 管理者ログイン
│   ├── attendance/
│   │   ├── index.blade.php      ← 勤怠一覧
│   │   └── show.blade.php       ← 勤怠詳細
│   ├── staff/
│   │   ├── index.blade.php      ← スタッフ一覧
│   │   └── attendance.blade.php ← スタッフ別勤怠一覧
│   ├── application/
│   │   └── index.blade.php      ← 申請一覧
│   └── approval/
│       └── edit.blade.php       ← 修正申請承認画面