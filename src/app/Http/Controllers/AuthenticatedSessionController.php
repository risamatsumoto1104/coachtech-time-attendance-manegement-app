<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // ログイン処理
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 認証が成功した場合
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 管理者ログイン
            if ($user && $user->is_admin) {
                return redirect()->route('admin.attendance.list.index');
            }

            // メール認証がされていない場合
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('message', 'メール認証が必要です。メールをご確認ください。');
            }

            // 一般ユーザーログイン
            return redirect()->route('attendance.create');
        }

        // 認証失敗
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
            'password' => 'ログイン情報が登録されていません。',
        ]);
    }


    // ログアウト処理
    public function destroy(Request $request)
    {
        // ログインしていない場合
        if (!Auth::check()) {
            // 管理者ログインページに接続している場合
            if ($request->is('admin/login')) {
                return redirect('/admin/login');
            }

            // 一般ユーザーログインページに接続している場合
            return redirect('/login');
        }

        // ログインしているユーザーを取得
        $user = Auth::user();

        // 管理者ログアウト
        if ($user && $user->is_admin) {
            Auth::logout();
            // セッションを無効化
            $request->session()->invalidate();
            // CSRFトークンを再生成
            $request->session()->regenerateToken();

            return redirect('/admin/login');
        }

        // 一般ユーザーログアウト
        Auth::logout();
        // セッションを無効化
        $request->session()->invalidate();
        // CSRFトークンを再生成
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
