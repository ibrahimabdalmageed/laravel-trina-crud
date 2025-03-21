<?php

namespace Trinavo\TrinaCrud\Traits;

use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

trait HasCrud
{
    /**
     * Get the fillable attributes for a specific CRUD action
     * 
     * @param string|CrudAction $action The CRUD action
     * @return array
     */
    public function getCrudFillable($action): array
    {
        $authorizationService = app(AuthorizationServiceInterface::class);
        
        // Convert string action to CrudAction enum if needed
        if (is_string($action)) {
            $action = CrudAction::tryFrom($action) ?? CrudAction::READ;
        }

        $fillable = $this->getFillable();
        $filteredFillable = [];
        foreach ($fillable as $field) {
            if ($authorizationService->isAttributeAuthorized($this, $field, $action)) {
                $filteredFillable[] = $field;
            }
        }

        return $filteredFillable;
    }
}
