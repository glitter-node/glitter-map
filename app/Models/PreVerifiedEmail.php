<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreVerifiedEmail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'verified_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
