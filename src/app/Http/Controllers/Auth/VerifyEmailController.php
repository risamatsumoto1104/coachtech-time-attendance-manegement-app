<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    // メール認証処理（一般ユーザー）
    public function verify(Request $request, $user_id, $hash)
    {
        // メール認証用の URL から user_id を取得して、そのユーザーをデータベースから検索
        // ユーザーが見つからない場合、findOrFail メソッドは 404 エラー
        $user = User::findOrFail($user_id);

        // ハッシュが一致しない場合403エラー
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'リンクが無効です。');
        }

        // 既に認証済みの場合
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('attendance.create')->with('status', '既に認証済みです。');
        }

        // 認証を完了
        $user->markEmailAsVerified();   //email_verified_at フィールドに現在時刻が保存
        event(new Verified($user));     //メール認証が完了したことをアプリケーションに通知するために Verified イベントが発火

        // 認証完了後にホームへリダイレクト
        return redirect()->route('attendance.create')->with('status', 'メール認証が完了しました。');
    }

    // メール認証再送信（一般ユーザー）
    public function resendVerificationEmail()
    {
        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            // メール認証の再送信
            $user->sendEmailVerificationNotification();
        }

        return back()->with('resent', true); // 再送信したことを通知
    }
}
