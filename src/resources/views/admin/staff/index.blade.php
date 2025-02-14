{{-- スタッフ一覧画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
    <div class="staff-container">

        <h2 class="staff-title">スタッフ一覧</h2>

        <table class="staff-table">
            <tr class="table-row-title">
                <th class="table-label">名前</th>
                <th class="table-label">メールアドレス</th>
                <th class="table-label">月次勤怠</th>
            </tr>
            <tr class="table-row-content">
                <td class="table-content">山田太郎</td>
                <td class="table-content">test@example.com</td>
                <td class="table-content">
                    <a class="detail-link" href="">詳細</a>
                </td>
            </tr>
        </table>
    </div>
@endsection
