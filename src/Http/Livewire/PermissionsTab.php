<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Models\ModelSchema;

class PermissionsTab extends Component
{

    public ?string $selectedRole = null;
    public string $modelFilter = '';
    public array $roles = [];
    public array $permissions = [];
    public bool $attributesModalVisible = false;
    public string $selectedModel = '';
    public array $selectedModelAttributes = [];
    /**
     * @var string[]
     */
    public array $models = [];

    public function mount()
    {
        $this->loadRoles();
        $this->loadModels();
    }

    public function render()
    {
        return view('trina-crud::livewire.permissions-tab');
    }

    public function loadRoles()
    {
        $this->roles = $this->getAuthorizationService()->getAllRoles();
    }

    public function loadModels()
    {
        $this->models = collect($this->getModelService()->getSchema())->map(function (ModelSchema $model) {
            return $model->getModelName();
        })->toArray();
    }


    public function getAuthorizationService(): AuthorizationServiceInterface
    {
        return App::make(AuthorizationServiceInterface::class);
    }

    public function updatedModelFilter()
    {
        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $this->permissions = [];
        foreach ($this->models as $model) {
            $model = str_replace('\\', '.', $model);
            if ($this->modelFilter && !str_contains(
                strtolower($model),
                strtolower(trim($this->modelFilter))
            )) {
                continue;
            }
            $read = $this->getAuthorizationService()->roleHasModelPermission(
                $model,
                CrudAction::READ,
                $this->selectedRole
            );
            $create = $this->getAuthorizationService()->roleHasModelPermission(
                $model,
                CrudAction::CREATE,
                $this->selectedRole
            );
            $update = $this->getAuthorizationService()->roleHasModelPermission(
                $model,
                CrudAction::UPDATE,
                $this->selectedRole
            );
            $delete = $this->getAuthorizationService()->roleHasModelPermission(
                $model,
                CrudAction::DELETE,
                $this->selectedRole
            );
            $this->permissions[$model] =
                [
                    CrudAction::READ->value => $read,
                    CrudAction::CREATE->value => $create,
                    CrudAction::UPDATE->value => $update,
                    CrudAction::DELETE->value => $delete,
                ];
        }
    }

    public function getModelService(): ModelServiceInterface
    {
        return App::make(ModelServiceInterface::class);
    }

    public function togglePermission($model, $action)
    {
        $authService = $this->getAuthorizationService();

        if ($authService->roleHasModelPermission($model, CrudAction::from($action), $this->selectedRole)) {
            $authService->setRoleModelPermission(
                $model,
                CrudAction::from($action),
                $this->selectedRole,
                false
            );
        } else {
            $authService->setRoleModelPermission(
                $model,
                CrudAction::from($action),
                $this->selectedRole,
                true
            );
        }

        $this->loadPermissions();
    }

    public function showAttributesModal($model)
    {
        $this->attributesModalVisible = true;
        $this->selectedModel = $model;
        $this->loadSelectedModelAttributes($model);
    }

    private function loadSelectedModelAttributes()
    {
        $attributes = collect($this->getModelService()->getSchema())
            ->first(function (ModelSchema $model) {
                return str_replace('\\', '.', $model->getModelName()) === $this->selectedModel;
            })
            ->getAllFields();


        /**
         * @var AuthorizationServiceInterface $authService
         */
        $authService = $this->getAuthorizationService();
        $this->selectedModelAttributes = [];
        foreach ($attributes as $attribute) {
            $read = $authService->roleHasAttributePermission(
                $this->selectedModel,
                $attribute,
                CrudAction::READ,
                $this->selectedRole
            );

            $create = $authService->roleHasAttributePermission(
                $this->selectedModel,
                $attribute,
                CrudAction::CREATE,
                $this->selectedRole
            );
            $update = $authService->roleHasAttributePermission(
                $this->selectedModel,
                $attribute,
                CrudAction::UPDATE,
                $this->selectedRole
            );
            $delete = $authService->roleHasAttributePermission(
                $this->selectedModel,
                $attribute,
                CrudAction::DELETE,
                $this->selectedRole
            );

            $this->selectedModelAttributes[$attribute] = [
                CrudAction::READ->value => $read,
                CrudAction::CREATE->value => $create,
                CrudAction::UPDATE->value => $update,
                CrudAction::DELETE->value => $delete,
            ];
        }
    }

    public function toggleAttributePermission($action, $attribute)
    {
        if ($this->getAuthorizationService()->roleHasAttributePermission(
            $this->selectedModel,
            $attribute,
            CrudAction::from($action),
            $this->selectedRole
        )) {

            $this->getAuthorizationService()->setRoleAttributePermission(
                $this->selectedModel,
                $attribute,
                CrudAction::from($action),
                $this->selectedRole,
                false
            );
        } else {
            $this->getAuthorizationService()->setRoleAttributePermission(
                $this->selectedModel,
                $attribute,
                CrudAction::from($action),
                $this->selectedRole,
                true
            );
        }

        $this->loadSelectedModelAttributes();
    }


    public function closeAttributesModal()
    {
        $this->attributesModalVisible = false;
    }
}
