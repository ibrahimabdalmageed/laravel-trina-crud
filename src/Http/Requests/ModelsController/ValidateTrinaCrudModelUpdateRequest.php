<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;


/**
 * @property string $model
 */

class ValidateTrinaCrudModelUpdateRequest extends ModelRequestValidator
{
    protected $action = 'update';

    public function rules(): array
    {
        return [];
    }
}
