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

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 管理者ログイン
            if ($user && $user->is_admin) {
                return redirect()->route('admin.attendance.list.index');
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

    // // メール認証通知の表示と送信（一般ユーザー）
    // public function showVerificationNotice()
    // {
    //     $user = Auth::user();

    //     // メール認証がまだされていない場合
    //     if (!$user->hasVerifiedEmail()) {
    //         // メール認証用の通知を送信
    //         $user->sendEmailVerificationNotification();
    //     }

    //     return view('auth.verify_email');
    // }

    // // メール認証処理（一般ユーザー）
    // public function verify(Request $request, $user_id, $hash)
    // {
    //     // メール認証用の URL から user_id を取得して、そのユーザーをデータベースから検索
    //     // ユーザーが見つからない場合、findOrFail メソッドは 404 エラー
    //     $user = User::findOrFail($user_id);

    //     // ハッシュが一致しない場合403エラー
    //     if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
    //         abort(403, 'リンクが無効です。');
    //     }

    //     // 既に認証済みの場合
    //     if ($user->hasVerifiedEmail()) {
    //         return redirect('/')->with('status', '既に認証済みです。');
    //     }

    //     // 認証を完了
    //     $user->markEmailAsVerified();   //email_verified_at フィールドに現在時刻が保存
    //     event(new Verified($user));     //メール認証が完了したことをアプリケーションに通知するために Verified イベントが発火

    //     // 認証完了後にホームへリダイレクト
    //     return redirect('/')->with('status', 'メール認証が完了しました。');
    // }

    // // メール認証再送信（一般ユーザー）
    // public function resendVerificationEmail()
    // {
    //     $user = Auth::user();

    //     if (!$user->hasVerifiedEmail()) {
    //         // メール認証の再送信
    //         $user->sendEmailVerificationNotification();
    //     }

    //     return back()->with('resent', true); // 再送信したことを通知
    // }

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
