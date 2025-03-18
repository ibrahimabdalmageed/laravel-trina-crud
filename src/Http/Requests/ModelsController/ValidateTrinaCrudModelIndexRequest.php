<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Trinavo\TrinaCrud\Services\AuthorizationService;

/**
 * @property string $model
 */

class ValidateTrinaCrudModelIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authService = App::make(AuthorizationService::class);
        return $authService->hasModelPermission($this->model ?? '', 'view');
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
