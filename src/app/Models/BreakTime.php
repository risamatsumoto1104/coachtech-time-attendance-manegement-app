<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance;

class BreakTime extends Model
{
    use HasFactory;

    // 主キー名を変更
    protected $primaryKey = 'break_id';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'break_start',
        'break_end',
    ];

    //　主：User(1)　⇔　従：BreakTime(N.0)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //　主：Attendance(1)　⇔　従：BreakTime(N.0)
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
}
