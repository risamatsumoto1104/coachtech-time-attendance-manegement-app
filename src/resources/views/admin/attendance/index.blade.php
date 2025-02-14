{{-- 勤怠一覧画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        <h2 class="attendance-title">2023年6月1日の勤怠</h2>

        <div class="attendance-nav">
            <div class="attendance-nav-previous">
                <img class="nav-icon-previous" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
                <p class="previous-day">前日</p>
            </div>
            <div class="attendance-nav-calendar" onclick="document.querySelector('.input-calendar').showPicker();">
                <img class="nav-icon-calendar" src="{{ asset('カレンダーアイコン.png') }}" alt="カレンダーアイコン">
                <input class="input-calendar" type="date">
            </div>
            <div class="attendance-nav-next">
                <p class="next-day">翌日</p>
                <img class="nav-icon-next" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
            </div>
        </div>

        <table class="attendance-table">
            <tr class="table-row-title">
                <th class="table-label">名前</th>
                <th class="table-label">出勤</th>
                <th class="table-label">退勤</th>
                <th class="table-label">休憩</th>
                <th class="table-label">合計</th>
                <th class="table-label">詳細</th>
            </tr>
            <tr class="table-row-content">
                <td class="table-content">山田太郎</td>
                <td class="table-content">09:00</td>
                <td class="table-content">18:00</td>
                <td class="table-content">1:00</td>
                <td class="table-content">8:00</td>
                <td class="table-content">
                    <a class="detail-link" href="">詳細</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
