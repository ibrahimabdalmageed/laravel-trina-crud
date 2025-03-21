<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Trinavo\TrinaCrud\Enums\CrudAction;

/**
 * @property string $model
 */
class ValidateTrinaCrudModelIndexRequest extends ModelRequestValidator
{
    protected $action = CrudAction::READ;

    public function rules(): array
    {
        return [];
    }
}
