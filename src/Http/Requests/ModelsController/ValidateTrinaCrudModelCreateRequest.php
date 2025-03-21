<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Trinavo\TrinaCrud\Enums\CrudAction;

/**
 * @property string $model
 */
class ValidateTrinaCrudModelCreateRequest extends ModelRequestValidator
{
    protected $action = CrudAction::CREATE;

    public function rules(): array
    {
        return [];
    }
}
