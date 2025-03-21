<?php

namespace Trinavo\TrinaCrud\Http\Requests\ModelsController;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

class ModelRequestValidator extends FormRequest
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var ModelServiceInterface
     */
    protected $modelService;

    /**
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    /**
     * @var CrudAction
     */
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->model = $this->route('model');
        $this->validateModelParameter();
    }

    private function validateModelParameter()
    {
        if (!$this->modelService->verifyModel($this->model)) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Invalid model',
                    'errors' => [
                        'model' => ['Model not found or not authorized']
                    ]
                ], 422)
            );
        }
    }
}
