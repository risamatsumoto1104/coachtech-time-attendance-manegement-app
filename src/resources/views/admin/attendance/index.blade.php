{{-- 勤怠一覧画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        <h2 class="attendance-title">
            {{ substr($currentDateFormatted ?? '', 0, 4) }}年{{ substr($currentDateFormatted ?? '', 5, 2) }}月{{ substr($currentDateFormatted ?? '', 8, 2) }}日の勤怠
        </h2>

        <div class="attendance-nav">
            <a class="attendance-nav-previous"
                href="{{ route('admin.attendance.list.index', ['date' => date('Y-m-d', strtotime($currentDateFormatted . ' -1 day'))]) }}">
                <img class="nav-icon-previous" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
                <p class="previous-day">前日</p>
            </a>
            <form class="attendance-nav-calendar" action="{{ route('admin.attendance.list.index') }}" method="GET"
                onclick="document.querySelector('.input-calendar').showPicker();">
                <img class="nav-icon-calendar" src="{{ asset('カレンダーアイコン.png') }}" alt="カレンダーアイコン">
                <input class="input-calendar" type="date" name="date" value="{{ $currentDateFormatted }}"
                    onchange="this.form.submit()">
            </form>
            <a class="attendance-nav-next"
                href="{{ route('admin.attendance.list.index', ['date' => date('Y-m-d', strtotime($currentDateFormatted . ' +1 day'))]) }}">
                <p class="next-day">翌日</p>
                <img class="nav-icon-next" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
            </a>
        </div>

        <table class="attendance-table">
            <tr class="table-row-title">
                <th class="table-label">名前</th>
                <th class="table-label">出勤</th>
                <th class="table-label">退勤</th>
                <th class="table-label">休憩</th>
                <th class="table-label">合計</th>
                <th class="table-label">詳細</th>
            </tr>
            @foreach ($attendances as $attendance)
                <tr class="table-row-content">
                    <td class="table-content">{{ $attendance->user->name }}</td>
                    <td class="table-content">{{ substr($attendance->clock_in ?? '', 11, 5) }}</td>
                    <td class="table-content">{{ substr($attendance->clock_out ?? '', 11, 5) }}</td>
                    <td class="table-content">{{ $attendance->totalBreakTime ?? '' }}</td>
                    <td class="table-content">{{ $attendance->totalWorkTime ?? '' }}</td>
                    <td class="table-content">
                        <a class="detail-link"
                            href="{{ route('admin.attendance.edit', ['user_id' => $attendance->user->user_id, 'date' => $currentDateFormatted]) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
