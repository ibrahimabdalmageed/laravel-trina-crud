<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;

/**
 * @property string $model
 */

class ValidateTrinaCrudModelCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'model' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!TrinaCrudModel::where('class_name', $value)->exists()) {
                        $fail("Invalid model");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'model.required' => 'The model field is required.',
            'model.string' => 'The model must be a valid string.',
        ];
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
