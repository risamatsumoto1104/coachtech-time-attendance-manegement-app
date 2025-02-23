{{-- 勤怠一覧画面（一般ユーザー） --}}
@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        <h2 class="attendance-title">勤怠一覧</h2>

        <div class="attendance-nav">
            <a class="attendance-nav-previous"
                href="{{ route('attendance.list.index', ['date' => date('Y-m', strtotime($currentDateYearMonth . ' -1 month'))]) }}">
                <img class="nav-icon-previous" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
                <p class="previous-month">前月</p>
            </a>
            <form class="attendance-nav-calendar" action="{{ route('attendance.list.index') }}" method="GET"
                onclick="document.querySelector('.input-calendar').showPicker();">
                <img class="nav-icon-calendar" src="{{ asset('カレンダーアイコン.png') }}" alt="カレンダーアイコン">
                <input class="input-calendar" type="month" name="month" value="{{ $currentDateYearMonth }}"
                    onchange="this.form.submit()">
            </form>
            <a class="attendance-nav-next"
                href="{{ route('attendance.list.index', ['date' => date('Y-m', strtotime($currentDateYearMonth . ' +1 month'))]) }}">
                <p class="next-month">翌月</p>
                <img class="nav-icon-next" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
            </a>
        </div>

        <table class="attendance-table">
            <tr class="table-row-title">
                <th class="table-label">日付</th>
                <th class="table-label">出勤</th>
                <th class="table-label">退勤</th>
                <th class="table-label">休憩</th>
                <th class="table-label">合計</th>
                <th class="table-label">詳細</th>
            </tr>
            @foreach ($attendances as $attendance)
                <tr class="table-row-content">
                    <td class="table-content">{{ $attendance->formatted_clock_in }}</td>
                    <td class="table-content">{{ substr($attendance->clock_in ?? '', 11, 5) }}</td>
                    <td class="table-content">{{ substr($attendance->clock_out ?? '', 11, 5) }}</td>
                    <td class="table-content">{{ $attendance->totalBreakTime ?? '' }}</td>
                    <td class="table-content">{{ $attendance->totalWorkTime ?? '' }}</td>
                    <td class="table-content">
                        <a class="detail-link"
                            href="{{ route('attendance.edit', ['user_id' => $attendance->user->user_id, 'date' => date('Y-m-d', strtotime($attendance->clock_in))]) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
