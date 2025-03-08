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
                    @foreach ($attendances as $attendance)
                        <p class="table-content-detail-name">{{ $attendance->user->name }}</p>
                    @endforeach
                </td>
            </tr>

            <tr class="table-row">
                <th class="table-label">日付</th>
                <td class="table-content">
                    <p class="table-content-detail">{{ substr($currentDateFormatted ?? '', 0, 4) }}年</p>
                    <p class="to"></p>
                    <p class="table-content-detail">
                        {{ substr($currentDateFormatted ?? '', 5, 2) }}月{{ substr($currentDateFormatted ?? '', 8, 2) }}日
                    </p>
                </td>
            </tr>

            <tr class="table-row">
                <th class="table-label">出勤・退勤</th>
                <td class="table-content">
                    @foreach ($attendances as $attendance)
                        <p class="table-content-detail">{{ substr($attendance->clock_in ?? '', 11, 5) }}</p>
                        <p class="to">～</p>
                        <p class="table-content-detail">{{ substr($attendance->clock_out ?? '', 11, 5) }}</p>
                    @endforeach
                </td>
            </tr>

            @foreach ($attendance->breakTimes as $index => $breakTime)
                <tr class="table-row">
                    <th class="table-label">休憩{{ $index + 1 }}</th>
                    <td class="table-content">
                        <p class="table-content-detail">{{ substr($breakTime->break_start ?? '', 11, 5) }}
                        </p>
                        <p class="to">～</p>
                        <p class="table-content-detail">{{ substr($breakTime->break_end ?? '', 11, 5) }}
                        </p>
                    </td>
                </tr>
            @endforeach

            <tr class="table-row">
                <th class="table-label">備考</th>
                <td class="table-content">
                    @foreach ($attendances as $attendance)
                        <p class="table-content-detail-text">{{ $attendance->remarks ?? '' }}
                        </p>
                    @endforeach
                </td>
            </tr>
        </table>

        <div class="form-submit">
            @foreach ($attendances as $attendance)
                @if ($stampRequest && $stampRequest->status === 'pending')
                    <form
                        action="{{ route('admin.stamp_correction_request.approve.update', ['user_id' => $userId, 'date' => $currentDateFormatted]) }}"
                        method="POST">
                        @csrf
                        @method('PATCH')

                        <input class="submit-button" type="submit" value="承認">

                        {{-- 実際に送信 --}}
                        <input class="submit-button" name=user_id type="hidden" value="{{ $attendance->user_id }}">
                        <input class="submit-button" name=attendance_id type="hidden"
                            value="{{ $attendance->attendance_id }}">
                        <input class="submit-button" name=status type="hidden" value="approved">
                    </form>
                @else
                    <button class="form-approved">認証済み</button>
                @endif
            @endforeach
        </div>
    </div>
@endsection
