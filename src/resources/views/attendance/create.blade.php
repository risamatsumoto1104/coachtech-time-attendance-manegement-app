{{-- 出勤登録画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
        @if ($attendanceStatus == 'before')
            {{-- 1.出勤前 --}}
            <div class="attendance-group">
                <button class="attendance-status">勤務外</button>
                <p class="attendance-date" id="attendance-date">
                    {{ substr($date ?? '', 0, 4) }}年{{ substr($date ?? '', 5, 2) }}月{{ substr($date ?? '', 8, 2) }}日({{ $weekday }})
                </p>
                <p class="attendance-time" id="attendance-time">{{ substr($date ?? '', 11, 5) }}</p>
                <form class="attendance-form" action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <input class="submit-clock-in" type="submit" value="出勤">
                    <input name="clock_in" type="hidden" value="2025-02-21 17:40:00">
                </form>
            </div>
        @elseif ($attendanceStatus == 'working')
            {{-- 2.出勤後 --}}
            <div class="attendance-group">
                <button class="attendance-status">出勤中</button>
                <p class="attendance-date" id="attendance-date">
                    {{ substr($date ?? '', 0, 4) }}年{{ substr($date ?? '', 5, 2) }}月{{ substr($date ?? '', 8, 2) }}日({{ $weekday }})
                </p>
                <p class="attendance-time" id="attendance-time">{{ substr($date ?? '', 11, 5) }}</p>
                <div class="form-group">
                    <form class="attendance-form" action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <input class="submit-clock-out" type="submit" value="退勤">
                        <input name="clock_out" type="hidden" value="2025-02-21 17:50:00">
                    </form>
                    <form class="attendance-form" action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <input class="submit-break-start" type="submit" value="休憩入">
                        <input name="break_start" type="hidden" value="2025-02-21 17:42:00">
                    </form>
                </div>
            </div>
        @elseif ($attendanceStatus == 'break')
            {{-- 3.休憩中 --}}
            <div class="attendance-group">
                <button class="attendance-status">休憩中</button>
                <p class="attendance-date" id="attendance-date">
                    {{ substr($date ?? '', 0, 4) }}年{{ substr($date ?? '', 5, 2) }}月{{ substr($date ?? '', 8, 2) }}日({{ $weekday }})
                </p>
                <p class="attendance-time" id="attendance-time">{{ substr($date ?? '', 11, 5) }}</p>
                <form class="attendance-form" action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <input class="submit-break-end" type="submit" value="休憩戻">
                    <input name="break_end" type="hidden" value="2025-02-21 17:43:00">
                </form>
            </div>
        @elseif ($attendanceStatus == 'checked_out')
            {{-- 4.退勤後 --}}
            <div class="attendance-group">
                <button class="attendance-status">退勤済</button>
                <p class="attendance-date" id="attendance-date">
                    {{ substr($date ?? '', 0, 4) }}年{{ substr($date ?? '', 5, 2) }}月{{ substr($date ?? '', 8, 2) }}日({{ $weekday }})
                </p>
                <p class="attendance-time" id="attendance-time">{{ substr($date ?? '', 11, 5) }}</p>
                <p class="attendance-massage">お疲れ様でした。</p>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        // 現在時刻をリアルタイムで更新
        function updateClock() {
            const now = new Date();
            const year = now.getFullYear();
            const month = ('0' + (now.getMonth() + 1)).slice(-2);
            const day = ('0' + now.getDate()).slice(-2);
            const hours = ('0' + now.getHours()).slice(-2);
            const minutes = ('0' + now.getMinutes()).slice(-2);
            const seconds = ('0' + now.getSeconds()).slice(-2);
            const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            const weekday = weekdays[now.getDay()];

            document.getElementById("attendance-date").innerHTML = `${year}年${month}月${day}日(${weekday})`;
            document.getElementById("attendance-time").innerHTML = `${hours}:${minutes}`;
        }

        // ページ読み込み後にリアルタイム更新を開始
        setInterval(updateClock, 1000); // 1秒ごとに更新
    </script>
@endsection
