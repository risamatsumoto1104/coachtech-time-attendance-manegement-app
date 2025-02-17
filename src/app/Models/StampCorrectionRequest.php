<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    // 主キー名を変更
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
    ];

    //　主：User(1)　⇔　従：StampCorrectionRequest(N.0)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //　主：Attendance(1)　⇔　従：StampCorrectionRequest(1)
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
}
