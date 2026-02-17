<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'roles', 'ip', 'method', 'url'
    ];

    protected $casts = [
        'roles' => 'array',
    ];
}
