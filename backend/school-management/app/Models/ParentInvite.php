<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentInvite extends Model
{
    protected $table = 'parent_invites';

    protected $fillable = [
        'student_id',
        'email',
        'token',
        'expires_at',
        'used_at',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
