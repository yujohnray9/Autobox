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
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
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
