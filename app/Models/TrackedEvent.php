<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackedEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'visitor_hash',
        'user_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}
