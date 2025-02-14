{{-- 申請一覧画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/index.css') }}">
@endsection

@section('content')
    <div class="request-container">

        <h2 class="request-title">申請一覧</h2>

        <div class="request-list">
            <a class="request-pending" href="">承認待ち</a>
            <a class="request-approval" href="">承認済み</a>
        </div>

        <table class="request-table">
            <tr class="table-row-title">
                <th class="table-label">状態</th>
                <th class="table-label">名前</th>
                <th class="table-label">対象日時</th>
                <th class="table-label">申請理由</th>
                <th class="table-label">申請日時</th>
                <th class="table-label">詳細</th>
            </tr>
            <tr class="table-row-content">
                <td class="table-content">認証待ち</td>
                <td class="table-content">テスト太郎</td>
                <td class="table-content">2023/06/01</td>
                <td class="table-content">遅延のため</td>
                <td class="table-content">2023/06/02</td>
                <td class="table-content">
                    <a class="detail-link" href="">詳細</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
