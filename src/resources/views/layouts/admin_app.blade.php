{{-- 共通画面（管理者） --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Coachtech TimeAttendanceManegementApp</title>
    <link rel="stylesheet" href="{{ asset('css/base/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base/admin-common.css') }}">
    @yield('css')
</head>

<body class="page-wrapper">
    <header class="header-container">

        <h1 class="header-logo-container">
            <img class="header-logo" src="{{ asset('logo.svg') }}" alt="COACHTECHロゴ">
        </h1>

        <nav class="header-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a class="nav-link" href="">勤怠一覧</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="">スタッフ一覧</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="">申請一覧</a>
                </li>
                <li class="nav-item">
                    <form action="{{ route('admin.logout') }}" method="post">
                        @csrf
                        <button class="nav-button" type="submit">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <main class="main-container">
        @yield('content')
    </main>

    @yield('scripts')
</body>

</html>
