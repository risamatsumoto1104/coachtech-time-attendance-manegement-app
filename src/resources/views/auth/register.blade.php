{{-- 会員登録画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register-container">

        <h2 class="register-title">会員登録</h2>

        <form class="register-form" action="{{ url('/register') }}" method="POST">
            @csrf
            <div class="form-group">
                <p class="form-label">名前</p>
                <input class="form-input" name="name" type="text" value="{{ old('name') }}" placeholder="テスト　太郎">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <p class="form-label">メールアドレス</p>
                <input class="form-input" name="email" type="text" value="{{ old('email') }}"
                    placeholder="test@example.com">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <p class="form-label">パスワード</p>
                <input class="form-input" name="password" type="password" placeholder="パスワードを入力してください">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <p class="form-label">パスワード確認</p>
                <input class="form-input" name="password_confirmation" type="password" placeholder="パスワードを入力してください">
                @error('password_confirmation')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-submit">
                <input class="submit-button" type="submit" value="登録する">
            </div>
        </form>

        <a class="login-link" href="{{ route('user.login') }}">ログインはこちら</a>
    </div>
@endsection
