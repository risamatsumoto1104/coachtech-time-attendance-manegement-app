{{-- 出勤登録画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
    <div class="attendance-container">
        {{-- 1.出勤前 --}}
        <div class="attendance-group">
            <button class="attendance-status">勤務外</button>
            <p class="attendance-date">2023年6月1日（木）</p>
            <p class="attendance-time">08:00</p>
            <form class="attendance-form" action="">
                <input class="submit-clock-in" type="submit" value="出勤">
            </form>
        </div>

        {{-- 2.出勤後 --}}
        <div class="attendance-group">
            <button class="attendance-status">出勤中</button>
            <p class="attendance-date">2023年6月1日（木）</p>
            <p class="attendance-time">08:00</p>
            <div class="form-group">
                <form class="attendance-form" action="">
                    <input class="submit-clock-out" type="submit" value="退勤">
                </form>
                <form class="attendance-form" action="">
                    <input class="submit-break-start" type="submit" value="休憩入">
                </form>
            </div>
        </div>

        {{-- 3.休憩中 --}}
        <div class="attendance-group">
            <button class="attendance-status">休憩中</button>
            <p class="attendance-date">2023年6月1日（木）</p>
            <p class="attendance-time">08:00</p>
            <form class="attendance-form" action="">
                <input class="submit-break-end" type="submit" value="休憩戻">
            </form>
        </div>

        {{-- 4.退勤後 --}}
        <div class="attendance-group">
            <button class="attendance-status">退勤済</button>
            <p class="attendance-date">2023年6月1日（木）</p>
            <p class="attendance-time">08:00</p>
            <p class="attendance-massage">お疲れ様でした。</p>
        </div>
    </div>
@endsection
