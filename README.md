# 勤怠管理アプリ

## 使用環境
- PHP 7.4.9
- Laravel 8.83.8
- MySQL 15.1
- Docker 27.5.1
- Composer 2.8.8
- Visual Studio Code (任意)

## 環境構築
1. git clone でリポジトリをクローン
2. docker-compose up -d --build
3. docker-compose exec php bash
4. composer install
5. .env.example をコピーして .env を作成
6. .env ファイルを必要に応じて修正
7. php artisan migrate
8. php artisan key:generate
9. composer require laravel/fortify

## ダミーデータ作成方法
- php artisan db:seed
- php artisan db:seed --class=ItemSeeder

## ログイン情報（開発用）
#### 管理者ユーザー
- メールアドレス：admin@example.com
- パスワード：password
#### 一般ユーザー
- メールアドレス：user@example.com
- パスワード：password

## テスト方法
- php artisan test