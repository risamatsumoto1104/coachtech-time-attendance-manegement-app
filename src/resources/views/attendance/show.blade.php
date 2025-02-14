{{-- 勤怠詳細画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
    <div class="request-approve-container">

        <h2 class="request-approve-title">勤怠詳細</h2>

        <table class="request-approve-table">
            <tr class="table-row">
                <th class="table-label">名前</th>
                <td class="table-content">
                    <p class="table-content-detail-name">山田太郎</p>
                </td>
            </tr>
            <tr class="table-row">
                <th class="table-label">日付</th>
                <td class="table-content">
                    <p class="table-content-detail">2023年</p>
                    <p class="to"></p>
                    <p class="table-content-detail">6月1日</p>
                </td>
            </tr>
            <tr class="table-row">
                <th class="table-label">出勤・退勤</th>
                <td class="table-content">
                    <p class="table-content-detail">9:00</p>
                    <p class="to">～</p>
                    <p class="table-content-detail">20:00</p>
                </td>
            </tr>
            <tr class="table-row">
                <th class="table-label">休憩</th>
                <td class="table-content">
                    <p class="table-content-detail">12:00</p>
                    <p class="to">～</p>
                    <p class="table-content-detail">13:00</p>
                </td>
            </tr>
            <tr class="table-row">
                <th class="table-label">休憩2</th>
                <td class="table-content">
                    <p class="table-content-detail">18:00</p>
                    <p class="to">～</p>
                    <p class="table-content-detail">18:30</p>
                </td>
            </tr>
            <tr class="table-row">
                <th class="table-label">備考</th>
                <td class="table-content">
                    <p class="table-content-detail-text">電車遅延の為</p>
                </td>
            </tr>
        </table>

        <p class="pending-message">*承認待ちのため修正はできません。</p>
    </div>
@endsection
