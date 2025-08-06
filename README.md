# 勤怠管理アプリ

## 使用環境

## 環境構築
1. git clone でリポジトリをクローン
2. docker-compose up -d --build
3. docker-compose exec php bash
4. composer install
5. .env.example をコピーして .env を作成
6. .env ファイルを必要に応じて修正
7. php artisan migrate
8. php artisan key:generate
9. php artisan db:seed
10. composer require laravel/fortify

