<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface OwnershipServiceInterface
{
    public function addOwnershipQuery(Builder $query, int $userId, string $modelClassName): Builder;
}
