<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    protected $fillable = [
        'user_id', 'roles', 'ip', 'method', 'url'
    ];

    protected $casts = [
        'roles' => 'array',
    ];
}
