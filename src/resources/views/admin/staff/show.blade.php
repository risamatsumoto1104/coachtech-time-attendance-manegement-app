{{-- スタッフ別勤怠一覧画面（管理者） --}}
@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/show.css') }}">
@endsection

@section('content')
    <div class="attendance-staff-container">

        <h2 class="attendance-staff-title">{{ $user ? $user->name : null }}さんの勤怠</h2>

        <div class="attendance-staff-nav">
            <a class="attendance-staff-nav-previous"
                href="{{ route('admin.attendance.staff.show', ['user_id' => $user->user_id, 'date' => date('Y-m', strtotime($currentDateYearMonth . ' -1 month'))]) }}">
                <img class="nav-icon-previous" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
                <p class="previous-month">前月</p>
            </a>
            <form class="attendance-staff-nav-calendar"
                action="{{ route('admin.attendance.staff.show', ['user_id' => $user->user_id]) }}" method="GET"
                onclick="document.querySelector('.input-calendar').showPicker();">
                <img class="nav-icon-calendar" src="{{ asset('カレンダーアイコン.png') }}" alt="カレンダーアイコン">
                <input class="input-calendar" type="month" name="month" value="{{ $currentDateYearMonth }}"
                    onchange="this.form.submit()">
            </form>
            <a class="attendance-staff-nav-next"
                href="{{ route('admin.attendance.staff.show', ['user_id' => $user->user_id, 'date' => date('Y-m', strtotime($currentDateYearMonth . ' +1 month'))]) }}">
                <p class="next-month">翌月</p>
                <img class="nav-icon-next" src="{{ asset('矢印アイコン.png') }}" alt="矢印アイコン">
            </a>
        </div>

        <table class="attendance-staff-table">
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
                    <td class="table-content">{{ $attendance->formatted_created_at }}</td>
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
