<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;

class Attendance extends Model
{
    use HasFactory;

    // 主キー名を変更
    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'remarks',
    ];

    //　主：Attendance(1)　⇔　従：BreakTime(N.0)
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    //　主：Attendance(1)　⇔　従：StampCorrectionRequest(1)
    public function stampCorrectionRequest()
    {
        return $this->hasOne(StampCorrectionRequest::class, 'attendance_id');
    }

    //　主：User(1)　⇔　従：Attendance(N.0)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
