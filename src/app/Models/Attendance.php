<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
