<?php

namespace Trinavo\TrinaCrud\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Spatie\Permission\Models\Role;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Models\ModelSchema;

class PermissionsTab extends Component
{
    public $models = [];
    public $rules = [];
    public $users = [];
    public $roles = [];

    public $selectedModel = '';
    public $selectedActions = [];
    public $isRole = 1; // Default to role
    public $selectedUserId = '';

    protected $validationRules = [
        'selectedModel' => 'required|string',
        'selectedActions' => 'required|array|min:1',
        'selectedUserId' => 'required',
    ];

    protected $messages = [
        'selectedModel.required' => 'Please select a model',
        'selectedActions.required' => 'Please select at least one action',
        'selectedActions.min' => 'Please select at least one action',
        'selectedUserId.required' => 'Please select a user or role',
    ];

    protected $listeners = ['refreshPermissions' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->loadModels();
        $this->loadRules();
        $this->loadUsers();
        $this->loadRoles();
    }

    public function render()
    {
        return view('trina-crud::livewire.permissions-tab');
    }

    public function loadModels()
    {
        $modelService = App::make(ModelServiceInterface::class);
        $this->models = collect($modelService->getSchema())->map(
            fn(ModelSchema $model) =>
            $model->getModelName()
        );
    }

    /**
     * Load users from the authorization service
     */
    protected function loadUsers()
    {
        /**
         * @var AuthorizationServiceInterface $authService
         */
        $authService = App::make(AuthorizationServiceInterface::class);
        $this->users = $authService->getAllUsers();
    }

    public function loadRoles()
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $roles = $authService->getAllRoles();

        // Add permissions count to each role
        foreach ($roles as &$role) {
            $roleModel = Role::find($role['id']);
            $role['permissionsCount'] = $roleModel ? $roleModel->permissions->count() : 0;
        }

        $this->roles = $roles;
    }

    public function loadRules()
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $this->rules = $authService->getRules();
    }

    public function addPermission()
    {
        $this->validate($this->validationRules);

        /**
         * @var AuthorizationServiceInterface $authService
         */
        $authService = App::make(AuthorizationServiceInterface::class);

        foreach ($this->selectedActions as $actionName) {
            $action = CrudAction::from($actionName);

            if ($this->isRole) {
                $authService->addRule($this->selectedModel, $action, $this->selectedUserId, true);
            } else {
                $authService->addRule($this->selectedModel, $action, $this->selectedUserId, false);
            }
        }

        $this->reset(['selectedActions', 'selectedUserId']);
        $this->loadRules();
        $this->emit('permissionsChanged');
        session()->flash('message', 'Permissions added successfully!');
    }

    public function deletePermission($permissionName)
    {
        $authService = App::make(AuthorizationServiceInterface::class);
        $authService->deleteRule($permissionName);

        $this->loadRules();
        $this->emit('permissionsChanged');
        session()->flash('message', 'Permission deleted successfully!');
    }
}
