<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Trinavo\TrinaCrud\Services\TrinaCrudAuthorizationService;

class ValidateTrinaCrudModelIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authService = App::make(TrinaCrudAuthorizationService::class);
        return $authService->hasModelPermission($this->model ?? '', 'view');
    }

    public function rules(): array
    {
        return [];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateModelParameter();
        });
    }

    private function validateModelParameter()
    {
        if (!TrinaCrudModel::where('class_name', $this->model)->exists()) {
            $this->errors()->add('model', 'Invalid model');
        }
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
