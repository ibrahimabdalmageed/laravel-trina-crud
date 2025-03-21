<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Trinavo\TrinaCrud\Enums\CrudAction;

/**
 * @property string $model
 */
class ValidateTrinaCrudModelUpdateRequest extends ModelRequestValidator
{
    protected $action = CrudAction::UPDATE;

    public function rules(): array
    {
        return [];
    }
}
