<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

class ValidateTrinaCrudModelIndexRequest extends ModelRequestValidator
{
    protected $action = 'view';

    public function rules(): array
    {
        return [];
    }
}
