{{-- 申請一覧画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/index.css') }}">
@endsection

@section('content')
    <div class="request-container">

        <h2 class="request-title">申請一覧</h2>

        <div class="request-list">
            <a class="request-pending"
                href="{{ route('admin.stamp_correction_request.list.index', ['tab' => 'pending']) }}">承認待ち</a>
            <a class="request-approval"
                href="{{ route('admin.stamp_correction_request.list.index', ['tab' => 'approved']) }}">承認済み</a>
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
            @if ($tab === 'pending')
                @foreach ($attendances as $attendance)
                    @if ($attendance->stampCorrectionRequest && $attendance->stampCorrectionRequest->status === 'pending')
                        <tr class="table-row-content">
                            <td class="table-content">承認待ち</td>
                            <td class="table-content">{{ $attendance->user->name }}</td>
                            <td class="table-content">{{ $attendance->formatted_clock_in }}</td>
                            <td class="table-content">{{ $attendance->remarks }}</td>
                            <td class="table-content">{{ $attendance->formatted_created_at }}</td>
                            <td class="table-content">
                                <a class="detail-link"
                                    href="{{ route('admin.stamp_correction_request.approve.edit', ['user_id' => $attendance->user->user_id, 'date' => $attendance->date]) }}">詳細</a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @elseif($tab === 'approved')
                @foreach ($attendances as $attendance)
                    @if ($attendance->stampCorrectionRequest && $attendance->stampCorrectionRequest->status === 'approved')
                        <tr class="table-row-content">
                            <td class="table-content">承認済み</td>
                            <td class="table-content">{{ $attendance->user->name }}</td>
                            <td class="table-content">{{ $attendance->formatted_clock_in }}</td>
                            <td class="table-content">{{ $attendance->remarks }}</td>
                            <td class="table-content">{{ $attendance->formatted_created_at }}</td>
                            <td class="table-content">
                                <a class="detail-link"
                                    href="{{ route('admin.stamp_correction_request.approve.edit', ['user_id' => $attendance->user->user_id, 'date' => $attendance->date]) }}">詳細</a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </table>
    </div>
@endsection
