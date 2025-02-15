<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 新規ユーザーの登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // 会員登録画面（一般ユーザー）
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン画面
        Fortify::loginView(function (Request $request) {
            // 管理者ログイン
            if ($request->route()->getName() === 'admin.login') {
                return view('admin.auth.login');
            }
            // 一般ユーザーログイン
            return view('auth.login');
        });

        // login処理の実行回数を1分あたり10回までに制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // メール認証誘導画面（一般ユーザー）
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        // FortifyLoginRequest を LoginRequest に置き換える。
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

        // ログイン後のリダイレクト先を切り替える処理
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse
            {
                public function toResponse()
                {
                    return redirect()->intended(auth()->user()->is_admin
                        // 管理者ログイン後、勤怠一覧画面
                        ? route('admin.attendance.list.index')
                        // 一般ユーザーログイン後、勤怠登録画面
                        : route('attendance.create'));
                }
            };
        });
    }
}
