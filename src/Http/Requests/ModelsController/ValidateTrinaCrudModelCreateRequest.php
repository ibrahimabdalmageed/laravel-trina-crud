<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;


class ValidateTrinaCrudModelCreateRequest extends ModelRequestValidator
{
    protected $action = 'create';

    public function rules(): array
    {
        return [];
    }
}
