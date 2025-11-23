<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Domain\Repositories\Command\AccessTokenRepInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class EloquentAccessTokenRepository implements AccessTokenRepInterface
{
    public function revokeToken(string $tokenId): void
    {
        $token = PersonalAccessToken::find($tokenId);

        if ($token) {
            $token->delete();
        }
    }

    public function deletionInvalidTokens(): int
    {
        $now = Carbon::now();
        $deleted = DB::table('personal_access_tokens')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->delete();
        return $deleted;
    }

}
