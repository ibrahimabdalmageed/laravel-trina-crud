<?php

namespace Trinavo\TrinaCrud\Services\OwnershipServices;

use Illuminate\Database\Eloquent\Builder;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;

class FieldOwnerService implements OwnershipServiceInterface
{
    public function addOwnershipQuery(Builder $query, int $userId, string $modelClassName): Builder
    {
        return $query->where(config('trina-crud.ownership_field'), $userId);
    }
}
