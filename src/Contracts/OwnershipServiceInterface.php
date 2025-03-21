<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;

interface OwnershipServiceInterface
{
    public function addOwnershipQuery(
        Builder|Relation $query,
        Model $model,
        string $action
    ): Builder|Relation;
}
