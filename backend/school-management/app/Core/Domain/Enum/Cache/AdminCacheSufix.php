<?php

namespace App\Core\Domain\Enum\Cache;

enum AdminCacheSufix : string
{
    case USERS = 'users';
    case ROLES = 'roles';
}