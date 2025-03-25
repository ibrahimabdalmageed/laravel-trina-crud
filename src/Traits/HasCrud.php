<?php

namespace Trinavo\TrinaCrud\Traits;

use Illuminate\Support\Facades\Schema;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

trait HasCrud
{
    /**
     * Get the fillable attributes for a specific CRUD action
     * 
     * @param CrudAction $action The CRUD action
     * @return array
     */
    public function getCrudFillable(CrudAction $action): array
    {
        $authorizationService = app(AuthorizationServiceInterface::class);

        $fillable = $this->getFillable();
        $filteredFillable = [];
        foreach ($fillable as $field) {
            if ($authorizationService->authHasAttributePermission(get_class($this), $field, $action)) {
                $filteredFillable[] = $field;
            }
        }

        return $filteredFillable;
    }

    /**
     * Get the validation rules for a specific CRUD action
     * 
     * @param CrudAction $action The CRUD action
     * @return array
     */
    public function getCrudRules(CrudAction $action): array
    {
        // Default implementation returns an empty array
        // Models should override this method to provide their specific validation rules
        return [];
    }


    public function getAllFields(): array
    {
        $columns = Schema::getColumnListing($this->getTable());
        return $columns;
    }
}
