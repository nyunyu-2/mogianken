<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    $user = $request->user();
                    $isAdmin = $user && $user->email === config('admin.email');

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return $isAdmin ? redirect('/admin/login') : redirect('/login');
                }
            };
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::registerView(function () {
            return view('user.auth.register');
        });

        Fortify::authenticateUsing(function ($request) {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません。'],
                ]);
            }

            if ($request->routeIs('admin.*') && $user->email !== config('admin.email')) {
                throw ValidationException::withMessages([
                    'email' => ['管理者アカウントのみログインできます。'],
                ]);
            }

            return $user;
        });


        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(1000); // 制限緩和（本番ではやらない）
        });

        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
    }
}
