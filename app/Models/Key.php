<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_name', 'room_name', 'description',
        'slot_number', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function currentBorrower()
    {
        return $this->transactions()
            ->where('action', 'borrow')
            ->where('status', 'success')
            ->whereNull('returned_at')
            ->with('user')
            ->latest()
            ->first();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'borrowed'  => 'amber',
            'missing'   => 'red',
            default     => 'gray',
        };
    }
}
