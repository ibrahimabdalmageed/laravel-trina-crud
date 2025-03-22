<div>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">TrinaCrud Permissions Manager</h1>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <button wire:click="switchTab('permissions')"
                        class="inline-block p-4 {{ $activeTab === 'permissions' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Manage Permissions
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="switchTab('roles')"
                        class="inline-block p-4 {{ $activeTab === 'roles' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Manage Roles
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="switchTab('matrix')"
                        class="inline-block p-4 {{ $activeTab === 'matrix' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Permission Matrix
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="switchTab('user-roles')"
                        class="inline-block p-4 {{ $activeTab === 'user-roles' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        User Roles
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        @if ($activeTab === 'permissions')
            @livewire('trina-crud::permissions-tab')
        @elseif ($activeTab === 'roles')
            @livewire('trina-crud::roles-tab')
        @elseif ($activeTab === 'matrix')
            @livewire('trina-crud::permission-matrix-tab')
        @elseif ($activeTab === 'user-roles')
            @livewire('trina-crud::user-roles-tab')
        @endif
    </div>
</div>
