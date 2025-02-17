{{--  勤怠詳細画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/edit.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        <h2 class="attendance-title">勤怠詳細</h2>

        @if (session('success'))
            <div>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div>
                {{ session('error') }}
            </div>
        @endif

        <form class="attendance-form"
            action="{{ route('admin.attendance.update', ['user_id' => $userId, 'date' => $currentDateFormatted]) }}"
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
                        <div class="content-wrapper">
                            <p class="content-year">{{ substr($currentDateFormatted ?? '', 0, 4) }}年</p>
                            <p class="to"></p>
                            <p class="content-date">
                                {{ substr($currentDateFormatted ?? '', 5, 2) }}月{{ substr($currentDateFormatted ?? '', 8, 2) }}日
                            </p>
                        </div>
                    </td>
                </tr>

                <tr class="table-row">
                    <th class="table-label">出勤・退勤</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            @foreach ($attendances as $attendance)
                                <!-- 表示用の入力フィールド（h:i形式） -->
                                <input class="content-input" name="clock_in" id="clock_in_id" type="text"
                                    value="{{ substr($attendance->clock_in ?? '', 11, 5) }}">
                                <p class="to">～</p>
                                <input class="content-input" name="clock_out" id="clock_out_id" type="text"
                                    value="{{ substr($attendance->clock_out ?? '', 11, 5) }}">

                                <!-- 実際に送信するdatetime値（hiddenフィールド） -->
                                <input type="hidden" name="clock_in" id="clock_in_id_hidden"
                                    value="{{ $attendance->clock_in }}">
                                <input type="hidden" name="clock_out" id="clock_out_id_hidden"
                                    value="{{ $attendance->clock_out }}">
                            @endforeach
                        </div>
                        @foreach (['clock_in', 'clock_out'] as $field)
                            @error($field)
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </td>
                </tr>

                <tr class="table-row">
                    <th class="table-label">休憩</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            @foreach ($attendances as $attendance)
                                <!-- 表示用の入力フィールド（h:i形式） -->
                                <input class="content-input" name="break_start[]" id="break_start_id" type="text"
                                    value="{{ substr($attendance->breakTimes[0]->break_start ?? '', 11, 5) }}">
                                <p class="to">～</p>
                                <input class="content-input" name="break_end[]" id="break_end_id" type="text"
                                    value="{{ substr($attendance->breakTimes[0]->break_end ?? '', 11, 5) }}">

                                <!-- 実際に送信するdatetime値（hiddenフィールド） -->
                                <input type="hidden" name="break_start[]" id="break_start_id_hidden"
                                    value="{{ $attendance->breakTimes[0]->break_start }}">
                                <input type="hidden" name="break_end[]" id="break_end_id_hidden"
                                    value="{{ $attendance->breakTimes[0]->break_end }}">
                            @endforeach
                        </div>
                        @foreach (['break_start', 'break_end'] as $field)
                            @error("{$field}.0")
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </td>
                </tr>

                <tr class="table-row">
                    <th class="table-label">休憩2</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            @foreach ($attendances as $attendance)
                                <!-- 表示用の入力フィールド（h:i形式） -->
                                <input class="content-input" name="break_start[]" id="break_start_id" type="text"
                                    value="{{ substr($attendance->breakTimes[1]->break_start ?? '', 11, 5) }}">
                                <p class="to">～</p>
                                <input class="content-input" name="break_end[]" id="break_end_id" type="text"
                                    value="{{ substr($attendance->breakTimes[1]->break_end ?? '', 11, 5) }}">

                                <!-- 実際に送信するdatetime値（hiddenフィールド） -->
                                <input type="hidden" name="break_start[]" id="break_start_id_hidden"
                                    value="{{ isset($attendance->breakTimes[1]) ? $attendance->breakTimes[1]->break_start : '' }}">
                                <input type="hidden" name="break_end[]" id="break_end_id_hidden"
                                    value="{{ isset($attendance->breakTimes[1]) ? $attendance->breakTimes[1]->break_end : '' }}">
                            @endforeach
                        </div>
                        @foreach (['break_start', 'break_end'] as $field)
                            @error("{$field}.1")
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </td>
                </tr>

                @foreach ($attendances as $attendance)
                    @foreach ($attendance->breakTimes->skip(2) as $index => $breakTime)
                        <tr class="table-row">
                            <th class="table-label">休憩{{ $index + 1 }}</th>
                            <td class="table-content">
                                <div class="content-wrapper">
                                    <!-- 表示用の入力フィールド（h:i形式） -->
                                    <input class="content-input" name="break_start[]" id="break_start_id" type="text"
                                        value="{{ substr($breakTime->break_start ?? '', 11, 5) }}">
                                    <p class="to">～</p>
                                    <input class="content-input" name="break_end[]" id="break_end_id" type="text"
                                        value="{{ substr($breakTime->break_end ?? '', 11, 5) }}">

                                    <!-- 実際に送信するdatetime値（hiddenフィールド） -->
                                    <input type="hidden" name="break_start[]" id="break_start_id_hidden"
                                        value="{{ isset($breakTime) && isset($breakTime->break_start) ? $breakTime->break_start : '' }}">
                                    <input type="hidden" name="break_end[]" id="break_end_id_hidden"
                                        value="{{ isset($breakTime) && isset($breakTime->break_end) ? $breakTime->break_end : '' }}">
                                </div>
                                @foreach (['break_start', 'break_end'] as $field)
                                    @error("{$field}. ($index + 1)")
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 変更を監視
            const clockInInput = document.getElementById('clock_in_id');
            const clockOutInput = document.getElementById('clock_out_id');
            const breakStartInput = document.getElementById('break_start_id');
            const breakEndInput = document.getElementById('break_end_id');

            const clockInHidden = document.getElementById('clock_in_id_hidden');
            const clockOutHidden = document.getElementById('clock_out_id_hidden');
            const breakStartHidden = document.getElementById('break_start_id_hidden');
            const breakEndHidden = document.getElementById('break_end_id_hidden');

            // 時間を適切なフォーマットに変換する関数
            function formatTimeToDatetime(time) {
                const timeParts = time.split(':');
                if (timeParts.length === 2) {
                    const hours = timeParts[0].padStart(2, '0'); // 時間を2桁に
                    const minutes = timeParts[1].padStart(2, '0'); // 分を2桁に
                    const now = new Date(); // 現在の日付を取得

                    // 0:00の場合など、時間部分が"0"だと0時として処理する
                    if (hours === '00') {
                        hours = '00';
                    }

                    // 現在の日付に時間と分を設定
                    now.setHours(hours);
                    now.setMinutes(minutes);
                    now.setSeconds(0);

                    // フォーマットされた時間を YYYY-MM-DD HH:mm:ss 形式に
                    const year = now.getFullYear();
                    const month = (now.getMonth() + 1).toString().padStart(2, '0'); // 月は0始まりなので +1
                    const day = now.getDate().toString().padStart(2, '0');
                    const formattedTime =
                        `${year}-${month}-${day} ${hours}:${minutes}:${now.getSeconds().toString().padStart(2, '0')}`;

                    return formattedTime;
                }
                return '';
            }

            // clock_in の変更時に hidden フィールドも更新
            if (clockInInput) {
                clockInInput.addEventListener('input', function() {
                    clockInHidden.value = formatTimeToDatetime(clockInInput.value);
                });
            }

            // clock_out の変更時に hidden フィールドも更新
            if (clockOutInput) {
                clockOutInput.addEventListener('input', function() {
                    clockOutHidden.value = formatTimeToDatetime(clockOutInput.value);
                });
            }

            // break_start の変更時に hidden フィールドも更新
            if (breakStartInput) {
                breakStartInput.addEventListener('input', function() {
                    breakStartHidden.value = formatTimeToDatetime(breakStartInput.value);
                });
            }

            // break_end の変更時に hidden フィールドも更新
            if (breakEndInput) {
                breakEndInput.addEventListener('input', function() {
                    breakEndHidden.value = formatTimeToDatetime(breakEndInput.value);
                });
            }

            // フォーム送信時に input フィールドの name 属性を削除
            const form = document.querySelector('form'); // フォームを選択
            if (form) {
                form.addEventListener('submit', function() {
                    // input フィールドの name 属性を削除
                    if (clockInInput) clockInInput.removeAttribute('name');
                    if (clockOutInput) clockOutInput.removeAttribute('name');
                    if (breakStartInput) breakStartInput.removeAttribute('name');
                    if (breakEndInput) breakEndInput.removeAttribute('name');
                });
            }
        });
    </script>
@endsection
