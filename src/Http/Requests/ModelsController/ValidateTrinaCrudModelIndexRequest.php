<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Contracts\TrinaCrudAuthorizationServiceInterface;
use Trinavo\TrinaCrud\Services\TrinaCrudModelHelper;
use Trinavo\TrinaCrud\Services\TrinaCrudModelService;

class ValidateTrinaCrudModelIndexRequest extends FormRequest
{
    /**
     * @var TrinaCrudModelHelper
     */
    protected $modelHelper;

    /**
     * @var TrinaCrudAuthorizationServiceInterface
     */
    protected $authService;

    public function __construct(
        TrinaCrudModelHelper $modelHelper,
        TrinaCrudAuthorizationServiceInterface $authService
    ) {
        $this->modelHelper = $modelHelper;
        $this->authService = $authService;
    }


    public function authorize(): bool
    {
        $authService = App::make(TrinaCrudAuthorizationServiceInterface::class);
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
        if (!$this->modelHelper->isModelExists($this->model)) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Invalid model',
                ], 422)
            );
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
