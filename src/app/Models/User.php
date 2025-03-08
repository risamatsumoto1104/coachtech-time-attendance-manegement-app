<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use App\Notifications\CustomVerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    // 主キー名を変更
    protected $primaryKey = 'user_id';

    public function getKey()
    {
        return $this->user_id;  // user_id を返す
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // メール認証用の通知をカスタマイズ
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmailNotification());
    }

    // role カラムが「admin」なら管理者
    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    //　主：User(1)　⇔　従：Attendance(N.0)
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    //　主：User(1)　⇔　従：BreakTime(N.0)
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'user_id');
    }

    //　主：User(1)　⇔　従：StampCorrectionRequest(N.0)
    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class, 'user_id');
    }
}
