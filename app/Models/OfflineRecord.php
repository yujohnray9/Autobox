<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineRecord extends Model
{
    protected $fillable = [
        'uid', 'type', 'user_id', 'slot_number', 'action', 'result', 'reason',
        'payload', 'occurred_at', 'status', 'sync_note', 'synced_at', 'synced_by',
    ];

    protected function casts(): array
    {
        return [
            'payload'     => 'array',
            'occurred_at' => 'datetime',
            'synced_at'   => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function syncedBy()
    {
        return $this->belongsTo(User::class, 'synced_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
