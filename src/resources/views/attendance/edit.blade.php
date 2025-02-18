{{-- 勤怠詳細画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/edit.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        <h2 class="attendance-title">勤怠詳細</h2>

        <form class="attendance-form" action="">
            <table class="attendance-table">
                <tr class="table-row">
                    <th class="table-label">名前</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            <p class="content-name">山田太郎</p>
                        </div>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">日付</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            <p class="content-year">2023年</p>
                            <p class="to"></p>
                            <p class="content-date">6月1日</p>
                        </div>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">出勤・退勤</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            <input class="content-input" name="clock_in" type="text" placeholder="9:00">
                            <p class="to">～</p>
                            <input class="content-input" name="clock_out" type="text" placeholder="20:00">
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
                            <input class="content-input" name="break_start" type="text" placeholder="12:00">
                            <p class="to">～</p>
                            <input class="content-input" name="break_end" type="text" placeholder="13:00">
                        </div>
                        @foreach (['break_start', 'break_end'] as $field)
                            @error($field)
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">休憩2</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            <input class="content-input" name="break_start" type="text" placeholder="18:00">
                            <p class="to">～</p>
                            <input class="content-input" name="break_end" type="text" placeholder="18:30">
                        </div>
                        @foreach (['break_start', 'break_end'] as $field)
                            @error($field)
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-label">備考</th>
                    <td class="table-content">
                        <div class="content-wrapper">
                            <textarea class="content-textarea" name="remarks" placeholder="修正理由を記入する"></textarea>
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
