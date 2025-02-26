{{-- 勤怠詳細画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/edit.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        <h2 class="attendance-title">勤怠詳細</h2>

        @if (!$hasPendingRequest || $hasApprovedRequest)
            <form class="attendance-form"
                action="{{ route('attendance.update', ['user_id' => $userId, 'date' => $currentDateFormatted]) }}"
                method="POST">
                @csrf
                @method('PATCH')

                <!-- 隠しフィールドとして送信 -->
                @foreach ($attendances as $attendance)
                    <input type="hidden" name="attendance_id" value="{{ $attendance->attendance_id }}">
                    <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
                    @foreach ($attendance->breakTimes as $breakTime)
                        <input type="hidden" name="break_id[]" value="{{ $breakTime->break_id }}">
                    @endforeach
                @endforeach

                <table class="attendance-table">
                    <tr class="table-row">
                        <th class="table-label">名前</th>
                        <td class="table-content">
                            <div class="content-wrapper">
                                @foreach ($attendances as $attendance)
                                    <p class="content-name">{{ $attendance->user->name }}</p>
                                @endforeach
                            </div>
                        </td>
                    </tr>

                    <tr class="table-row">
                        <th class="table-label">日付</th>
                        <td class="table-content">
                            <div class="content-wrapper" onclick="document.querySelector('.date-input').showPicker();">
                                <input class="date-input" name="current_date" id="date-input" type="date"
                                    value="{{ $currentDateFormatted }}">
                                <p class="to"></p>
                            </div>
                        </td>
                    </tr>

                    <tr class="table-row">
                        <th class="table-label">出勤・退勤</th>
                        <td class="table-content">
                            <div class="content-wrapper">
                                @foreach ($attendances as $index => $attendance)
                                    <!-- 表示用の入力フィールド（h:i形式） -->
                                    <input class="content-input" name="clock_in" type="text"
                                        value="{{ substr($attendance->clock_in ?? '', 11, 5) }}"
                                        data-index="{{ $index }}" data-type="start">
                                    <p class="to">～</p>
                                    <input class="content-input" name="clock_out" type="text"
                                        value="{{ substr($attendance->clock_out ?? '', 11, 5) }}"
                                        data-index="{{ $index }}" data-type="end">

                                    <!-- 実際に送信するdatetime値（hiddenフィールド） -->
                                    <input type="hidden" name="clock_in" value="{{ $attendance->clock_in }}"
                                        data-index="{{ $index }}" data-type="hidden_start">
                                    <input type="hidden" name="clock_out" value="{{ $attendance->clock_out }}"
                                        data-index="{{ $index }}" data-type="hidden_end">
                                @endforeach
                            </div>
                            @foreach (['clock_in', 'clock_out'] as $field)
                                @error($field)
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </td>
                    </tr>

                    @foreach ($attendance->breakTimes as $index => $breakTime)
                        <tr class="table-row">
                            <th class="table-label">休憩{{ $index + 1 }}</th>
                            <td class="table-content">
                                <div class="content-wrapper">
                                    <!-- 表示用の入力フィールド（h:i形式） -->
                                    <input class="content-input" type="text" data-index="{{ $index + 1 }}"
                                        data-type="start" value="{{ substr($breakTime->break_start ?? '', 11, 5) }}">
                                    <p class="to">～</p>
                                    <input class="content-input" type="text" data-index="{{ $index + 1 }}"
                                        data-type="end" value="{{ substr($breakTime->break_end ?? '', 11, 5) }}">

                                    <!-- 実際に送信するdatetime値（hiddenフィールド） -->
                                    <input class="content-input" type="hidden" name="break_start[]"
                                        data-index="{{ $index + 1 }}" data-type="hidden_start"
                                        value="{{ $breakTime->break_start }}">
                                    <input class="content-input" type="hidden" name="break_end[]"
                                        data-index="{{ $index + 1 }}" data-type="hidden_end"
                                        value="{{ $breakTime->break_end }}">
                                </div>
                                @foreach (['break_start', 'break_end'] as $field)
                                    @error("{$field}." . ($index + 1))
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            </td>
                        </tr>
                    @endforeach

                    <tr class="table-row">
                        <th class="table-label">備考</th>
                        <td class="table-content">
                            <div class="content-wrapper">
                                @foreach ($attendances as $attendance)
                                    <textarea class="content-textarea" name="remarks">{{ $attendance->remarks ?? '' }}</textarea>
                                @endforeach
                            </div>
                            @error('remarks')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div class="form-submit">
                    <input class="submit-button" type="submit" value="修正">
                </div>
            </form>
        @elseif($hasPendingRequest)
            <table class="attendance-table">
                <tr class="table-row">
                    <th class="table-label">名前</th>
                    <td class="pending-table-content">
                        <p class="pending-table-content-detail-name">山田太郎</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">日付</th>
                    <td class="pending-table-content">
                        <p class="pending-table-content-detail">2023年</p>
                        <p class="pending-to"></p>
                        <p class="pending-table-content-detail">6月1日</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">出勤・退勤</th>
                    <td class="pending-table-content">
                        <p class="pending-table-content-detail">9:00</p>
                        <p class="pending-to">～</p>
                        <p class="pending-table-content-detail">20:00</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">休憩</th>
                    <td class="pending-table-content">
                        <p class="pending-table-content-detail">12:00</p>
                        <p class="pending-to">～</p>
                        <p class="pending-table-content-detail">13:00</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">休憩2</th>
                    <td class="pending-table-content">
                        <p class="pending-table-content-detail">18:00</p>
                        <p class="pending-to">～</p>
                        <p class="pending-table-content-detail">18:30</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">備考</th>
                    <td class="pending-table-content">
                        <p class="pending-table-content-detail-text">電車遅延の為</p>
                    </td>
                </tr>
            </table>

            <p class="pending-message">*承認待ちのため修正はできません。</p>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.content-input[data-type="start"], .content-input[data-type="end"]').forEach(
                input => {
                    input.addEventListener('input', function() {
                        const index = this.dataset.index; // インデックスを取得
                        const type = this.dataset.type; // start または end を取得

                        // 入力された時間を取得
                        const timeValue = this.value;

                        // 対応する隠しフィールドを取得して更新
                        const hiddenInput = document.querySelector(
                            `input[data-index="${index}"][data-type="hidden_${type}"]`);
                        if (hiddenInput) {
                            const originalValue = hiddenInput.value; // 元の日時を取得
                            const newValue = originalValue.slice(0, 11) + timeValue; // H:M を入れ替える
                            hiddenInput.value = newValue; // 更新
                        }
                    });
                });
        });
    </script>
@endsection
