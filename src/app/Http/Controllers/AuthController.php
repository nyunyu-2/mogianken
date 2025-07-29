<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    public function register()
    {
        return view('user.auth.register');
    }

    public function login()
    {
        return view('user.auth.login');
    }

    public function AdminLogin()
    {
        return view('admin.auth.login');
    }

    public function adminAuthenticate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 管理者メールアドレスだけログイン許可
        if ($request->email === config('admin.email')) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user);
                return redirect()->route('admin.attendances.index');
            }
        }

        return back()->withErrors([
            'email' => '認証に失敗しました。',
        ]);
    }
}
