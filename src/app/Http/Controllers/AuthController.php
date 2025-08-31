<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function login()
    {
        return view('user.auth.login');
    }

    public function AdminLogin()
    {
        return view('admin.auth.login');
    }

    public function userAuthenticate(LoginRequest $request)
    {
        // 管理者アカウント弾く
        if ($request->email === config('admin.email')) {
            return back()->withErrors(['email' => 'このアカウントは管理者専用です。管理者ログイン画面からログインしてください。']);
        }

        if (Auth::attempt($request->only('email','password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('user.attendance.create');
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function adminAuthenticate(LoginRequest $request)
    {
        if ($request->email !== config('admin.email')) {
            return back()->withErrors(['email' => 'このアカウントは管理者専用ではありません。']);
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            return redirect()->route('admin.attendances.index');
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function userLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // 一般ログイン画面へ
    }

    public function adminLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login'); // 管理者ログイン画面へ
    }
}
