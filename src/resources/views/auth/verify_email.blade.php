{{-- メール認証誘導画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
    <div class="email-verification-container">

        <p class="email-verification-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください
        </p>

        <form class="guidance-form" action="http://localhost:8025/" method="GET">
            <button class="guidance-button" type="submit">認証はこちらから</button>
        </form>

        <form class="email-verification-form" action="{{ url('/email/resend') }}" method="POST">
            @csrf
            <button class="email-verification-button" type="submit">認証メールを再送する</button>
        </form>
    </div>
@endsection
