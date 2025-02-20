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
            @foreach ($users as $user)
                <tr class="table-row-content">
                    <td class="table-content">{{ $user->name }}</td>
                    <td class="table-content">{{ $user->email }}</td>
                    <td class="table-content">
                        <a class="detail-link"
                            href="{{ route('admin.attendance.staff.show', ['user_id' => $user->user_id]) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
