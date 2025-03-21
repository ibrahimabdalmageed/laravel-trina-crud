<?php

namespace Trinavo\TrinaCrud\Services\OwnershipServices;

use Illuminate\Database\Eloquent\Builder;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;

class OwnableService implements OwnershipServiceInterface
{
    public function addOwnershipQuery(Builder $query, int $userId, string $modelClassName): Builder
    {
        return $query->ownedBy($userId);
    }
}
