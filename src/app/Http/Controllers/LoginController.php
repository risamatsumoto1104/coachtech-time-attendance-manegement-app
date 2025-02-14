<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    // ログイン画面を表示（管理者）
    public function showLoginForm(Request $request)
    {
        return view('admin.auth.login');
    }
}
