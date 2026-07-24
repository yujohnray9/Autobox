<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'key_id', 'action', 'status',
        'notes', 'borrowed_at', 'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at'  => 'datetime',
            'returned_at'  => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function key()
    {
        return $this->belongsTo(Key::class);
    }
}
