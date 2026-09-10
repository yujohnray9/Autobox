<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'department', 'employee_id',
        'qr_token', 'is_active',
        'login_otp_code', 'login_otp_expires_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'login_otp_expires_at' => 'datetime',
        ];
    }

    public function generateLoginOtp(): string
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'login_otp_code'       => $otp,
            'login_otp_expires_at' => now()->addMinutes(10),
        ]);
        return $otp;
    }

    public function clearLoginOtp(): void
    {
        $this->update([
            'login_otp_code'       => null,
            'login_otp_expires_at' => null,
        ]);
    }

    public function isLoginOtpValid(string $code): bool
    {
        if (empty($this->login_otp_code) || empty($this->login_otp_expires_at)) {
            return false;
        }

        if (now()->isAfter($this->login_otp_expires_at)) {
            return false;
        }

        return hash_equals((string) $this->login_otp_code, trim($code));
    }

    public function generateQrToken(): string
    {
        $token = Str::uuid()->toString();
        $this->update(['qr_token' => $token]);
        return $token;
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
