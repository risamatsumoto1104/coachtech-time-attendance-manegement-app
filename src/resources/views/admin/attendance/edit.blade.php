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
                    <td class="table-content" id="date-wrapper">
                        <div class="content-wrapper">
                            <p class="content-year">{{ substr($currentDateFormatted ?? '', 0, 4) }}年</p>
                            <p class="to"></p>
                            <p class="content-date">
                                {{ substr($currentDateFormatted ?? '', 5, 2) }}月{{ substr($currentDateFormatted ?? '', 8, 2) }}日
                            </p>
                        </div>
                        <input class="date-input" type="date" name="current_date" id="date-input"
                            value="{{ $currentDateFormatted }}" style="display: none;" required>
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

                @foreach ($attendances as $attendance)
                    @for ($i = 0; $i < 2; $i++)
                        {{-- 休憩1, 休憩2は必ず表示 --}}
                        <tr class="table-row">
                            <th class="table-label">休憩{{ $i + 1 }}</th>
                            <td class="table-content">
                                <div class="content-wrapper">
                                    <input class="content-input" type="text" data-index="{{ $i }}"
                                        data-type="start"
                                        value="{{ isset($attendance->breakTimes[$i]) ? substr($attendance->breakTimes[$i]->break_start, 11, 5) : '' }}">
                                    <p class="to">～</p>
                                    <input class="content-input" type="text" data-index="{{ $i }}"
                                        data-type="end"
                                        value="{{ isset($attendance->breakTimes[$i]) ? substr($attendance->breakTimes[$i]->break_end, 11, 5) : '' }}">

                                    <input class="content-input" type="hidden" name="break_start[]"
                                        data-index="{{ $i }}" data-type="hidden_start"
                                        value="{{ $attendance->breakTimes[$i]->break_start ?? '' }}">
                                    <input class="content-input" type="hidden" name="break_end[]"
                                        data-index="{{ $i }}" data-type="hidden_end"
                                        value="{{ $attendance->breakTimes[$i]->break_end ?? '' }}">
                                </div>
                                @foreach (['break_start', 'break_end'] as $field)
                                    @error("{$field}." . $i)
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            </td>
                        </tr>
                    @endfor

                    @foreach ($attendance->breakTimes->skip(2) as $index => $breakTime)
                        {{-- 休憩3以降はデータがある場合のみ表示 --}}
                        <tr class="table-row">
                            <th class="table-label">休憩{{ $index + 3 }}</th>
                            <td class="table-content">
                                <div class="content-wrapper">
                                    <input class="content-input" type="text" data-index="{{ $index + 2 }}"
                                        data-type="start" value="{{ substr($breakTime->break_start ?? '', 11, 5) }}">
                                    <p class="to">～</p>
                                    <input class="content-input" type="text" data-index="{{ $index + 2 }}"
                                        data-type="end" value="{{ substr($breakTime->break_end ?? '', 11, 5) }}">

                                    <input class="content-input" type="hidden" name="break_start[]"
                                        data-index="{{ $index + 2 }}" data-type="hidden_start"
                                        value="{{ $breakTime->break_start }}">
                                    <input class="content-input" type="hidden" name="break_end[]"
                                        data-index="{{ $index + 2 }}" data-type="hidden_end"
                                        value="{{ $breakTime->break_end }}">
                                </div>
                                @foreach (['break_start', 'break_end'] as $field)
                                    @error("{$field}." . ($index + 2))
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

            // 日付を選択するためのinputを設定
            let dateWrapper = document.getElementById('date-wrapper');
            let dateInput = document.getElementById('date-input');

            // 日付表示部分をクリックしたときの処理
            dateWrapper.addEventListener('click', function() {
                dateInput.style.display = 'block'; // 日付入力フィールドを表示
                dateInput.focus(); // フォーカスを当てる
            });

            // 日付が選択された後の処理
            dateInput.addEventListener('change', function() {
                let datePart = this.value; // 選択された日付をdatePartに更新
                this.style.display = 'none'; // 日付入力フィールドを隠す

                // 日付をフォーマットしてp要素を更新
                let year = datePart.substring(0, 4);
                let month = datePart.substring(5, 7);
                let day = datePart.substring(8, 10);

                // 年と日付のp要素を取得
                let yearElement = dateWrapper.querySelector('.content-year');
                let dateElement = dateWrapper.querySelector('.content-date');

                // p要素の内容を更新
                yearElement.textContent = year + '年';
                dateElement.textContent = month + '月' + day + '日';

                // current_dateのinputの値を更新
                this.value = datePart;
            });
        });
    </script>
@endsection
