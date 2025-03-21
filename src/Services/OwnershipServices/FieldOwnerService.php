<?php

namespace Trinavo\TrinaCrud\Services\OwnershipServices;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;

class FieldOwnerService implements OwnershipServiceInterface
{
    public function addOwnershipQuery(
        Builder|Relation $query,
        Model $model,
        string $action
    ): Builder|Relation {
        return $query->where(config('trina-crud.ownership_field'), Auth::id());
    }
}
