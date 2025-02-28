<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    // 申請一覧画面を表示（一般ユーザー）
    public function index(Request $request)
    {
        // ログインしているユーザーを取得
        $user = Auth::user();

        $attendances = Attendance::with('user', 'stampCorrectionRequest')
            ->where('user_id', $user->user_id)
            ->get();

        $attendances->each(function ($attendance) {
            $clockIn = $attendance->clock_in;
            $clockInDateTime = new \DateTime($clockIn);
            $attendance->formatted_clock_in = $clockInDateTime->format('Y/m/d');
            $attendance->date = $clockInDateTime->format('Y-m-d');

            if ($attendance->stampCorrectionRequest) {
                $requestCreatedAt = $attendance->stampCorrectionRequest->created_at;
                $requestCreatedAtDateTime = new \DateTime($requestCreatedAt);
                $attendance->formatted_created_at = $requestCreatedAtDateTime->format('Y/m/d');
            }
        });

        $tab = $request->get('tab', 'pending'); //デフォルト

        return view('request.index', compact('attendances', 'tab'));
    }
}
