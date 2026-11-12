<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class InboundLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'service',
        'message',
        'locale',
        'status',
        'consented_at',
    ];

    protected function casts(): array
    {
        return [
            'consented_at' => 'immutable_datetime',
        ];
    }
}
