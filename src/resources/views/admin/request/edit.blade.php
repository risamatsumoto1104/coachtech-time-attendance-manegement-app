{{--  修正申請承認画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/edit.css') }}">
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

        <div class="form-submit">
            <input class="submit-button" type="submit" value="承認">
            <button class="form-approved">認証済み</button>
        </div>
    </div>
@endsection
