{{-- ログイン画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login-container">

        <h2 class="login-title">ログイン</h2>

        <form class="login-form" action="" method="POST">
            @csrf
            <div class="form-group">
                <p class="form-label">メールアドレス</p>
                <input class="form-input" name="email" type="text" value="{{ old('email') }}"
                    placeholder="test@example.com">
                <p class="error-message">エラーメッセージを表示する</p>
            </div>

            <div class="form-group">
                <p class="form-label">パスワード</p>
                <input class="form-input" name="password" type="password" placeholder="パスワードを入力してください">
                <p class="error-message">エラーメッセージを表示する</p>
            </div>

            <div class="form-submit">
                <input class="submit-button" type="submit" value="ログインする">
            </div>
        </form>

        <a class="login-link" href="{{ url('/register') }}">会員登録はこちら</a>
    </div>
@endsection
