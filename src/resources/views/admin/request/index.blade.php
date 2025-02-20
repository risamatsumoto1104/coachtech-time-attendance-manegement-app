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
                @foreach ($stampCorrectionRequests as $stampCorrectionRequest)
                    <tr class="table-row-content">
                        <td class="table-content">{{ $stampCorrectionRequest->status }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->user->name }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->attendance->created_at }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->attendance->remarks }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->created_at }}</td>
                        <td class="table-content">
                            <a class="detail-link"
                                href="{{ route('admin.stamp_correction_request.approve.edit', ['request_id' => $stampCorrectionRequest->request_id]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            @elseif($tab === 'approved')
                @foreach ($stampCorrectionRequests as $stampCorrectionRequest)
                    <tr class="table-row-content">
                        <td class="table-content">{{ $stampCorrectionRequest->status }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->user->name }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->attendance->created_at }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->attendance->remarks }}</td>
                        <td class="table-content">{{ $stampCorrectionRequest->created_at }}</td>
                        <td class="table-content">
                            <a class="detail-link"
                                href="{{ route('admin.stamp_correction_request.approve.edit', ['request_id' => $stampCorrectionRequest->request_id]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>
    </div>
@endsection
