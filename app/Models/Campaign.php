<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'from', 'to', 'total_budget', 'daily_budget', 'creatives'
    ];

    protected $casts = [
        'from' => 'date',
        'to' => 'date',
        'creatives' => 'array',
    ];
}
