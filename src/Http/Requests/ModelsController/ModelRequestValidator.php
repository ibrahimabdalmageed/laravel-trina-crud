<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;

class ModelRequestValidator extends FormRequest
{
    /**
     * @var ModelHelperInterface
     */
    protected $modelHelper;

    /**
     * @var ModelServiceInterface
     */
    protected $modelService;

    /**
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    protected $action;

    public function authorize(): bool
    {
        return $this->authorizationService->hasModelPermission($this->model ?? '', $this->action);
    }

    public function __construct(
        ModelServiceInterface $modelService,
        AuthorizationServiceInterface $authorizationService
    ) {
        $this->modelService = $modelService;
        $this->authorizationService = $authorizationService;
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
        if (!$this->modelService->verifyModel($this->model)) {
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
