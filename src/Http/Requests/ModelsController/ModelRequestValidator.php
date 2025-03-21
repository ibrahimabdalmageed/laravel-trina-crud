<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Services\TrinaCrudModelHelper;
use Trinavo\TrinaCrud\Contracts\TrinaCrudAuthorizationServiceInterface;
use Trinavo\TrinaCrud\Services\TrinaCrudModelService;

class ModelRequestValidator extends FormRequest
{
    /**
     * @var TrinaCrudModelHelper
     */
    protected $modelHelper;

    /**
     * @var TrinaCrudModelService
     */
    protected $modelService;

    protected $action;

    public function authorize(): bool
    {
        return $this->modelService->hasModelPermission($this->model ?? '', $this->action);
    }

    public function __construct(
        TrinaCrudModelService $modelService,
    ) {
        $this->modelService = $modelService;
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
