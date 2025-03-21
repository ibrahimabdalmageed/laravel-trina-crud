<?php

namespace Trinavo\TrinaCrud\Traits;

use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

trait HasCrud
{
    public function getCrudFillable($action): array
    {
        $authorizationService = app(AuthorizationServiceInterface::class);

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
