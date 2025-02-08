<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
