<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'body',
        'category',
        'priority',
        'sentiment',
        'summary',
        'analyzed_at',
        'analysis_error',
    ];

    protected function casts(): array
    {
        return [
            'analyzed_at' => 'datetime',
        ];
    }
}
