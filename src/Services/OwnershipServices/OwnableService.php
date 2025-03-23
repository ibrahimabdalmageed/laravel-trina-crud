<?php

namespace Trinavo\TrinaCrud\Services\OwnershipServices;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use RuntimeException;

class OwnableService implements OwnershipServiceInterface
{

    public function addOwnershipQuery(
        Builder|Relation $query,
        Model $model,
        string $action
    ): Builder|Relation {

        if (!method_exists($model, 'scopeMine')) {
            throw new RuntimeException("Ownable trait not found. Please install it: composer require trinavo/laravel-ownable");
        }

        return $query->mine();
    }
}
