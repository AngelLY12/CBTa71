<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = ['user_id', 'token', 'expires_at', 'revoked'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool {
        return $this->expires_at->isPast() || $this->revoked;
    }

    public function revoke(): void {
        $this->revoked = true;
        $this->save();
    }
}
