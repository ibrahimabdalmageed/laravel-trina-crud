<div>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">TrinaCrud Permissions Manager</h1>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'permissions')"
                        class="inline-block p-4 {{ $activeTab === 'permissions' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Manage Permissions
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'roles')"
                        class="inline-block p-4 {{ $activeTab === 'roles' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Manage Roles
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'matrix')"
                        class="inline-block p-4 {{ $activeTab === 'matrix' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        Permission Matrix
                    </button>
                </li>
            </ul>
        </div>

        <!-- Permissions Tab -->
        @if ($activeTab === 'permissions')
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-xl font-semibold mb-4">Add New Permission</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="model">
                            Model
                        </label>
                        <select wire:model="selectedModel" id="model"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select a model</option>
                            @foreach ($models as $model)
                                <option value="{{ $model }}">{{ $model }}</option>
                            @endforeach
                        </select>
                        @error('selectedModel')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Actions
                        </label>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="selectedActions" value="read"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Read</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="selectedActions" value="create"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Create</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="selectedActions" value="update"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Update</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="selectedActions" value="delete"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Delete</span>
                            </label>
                        </div>
                        @error('selectedActions')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Assign To
                        </label>
                        <div class="flex items-center mb-2">
                            <input type="radio" wire:model="isRole" id="user" value="0" class="mr-2">
                            <label for="user">User</label>

                            <input type="radio" wire:model="isRole" id="role" value="1" class="ml-4 mr-2">
                            <label for="role">Role</label>
                        </div>

                        @if ($isRole)
                            <select wire:model="selectedUserId"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select a role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <select wire:model="selectedUserId"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select a user</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user['id'] }}">{{ $user['name'] }} ({{ $user['email'] }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('selectedUserId')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <button wire:click="addPermission"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Add Permission
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-xl font-semibold mb-4">Current Permissions</h2>

                @if (count($rules) > 0)
                    @foreach ($rules as $model => $actions)
                        <div class="mb-6 border rounded p-4">
                            <h3 class="text-lg font-medium mb-4">{{ $model }}</h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th
                                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Action</th>
                                            <th
                                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Assigned To</th>
                                            <th
                                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($actions as $action => $details)
                                            <tr>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ ucfirst($action) }}
                                                </td>
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    @if (count($details['roles']) > 0)
                                                        <div class="mb-1">
                                                            <span class="font-semibold">Roles:</span>
                                                            {{ implode(', ', $details['roles']) }}
                                                        </div>
                                                    @endif

                                                    @if (count($details['users']) > 0)
                                                        <div>
                                                            <span class="font-semibold">Users:</span>
                                                            {{ implode(', ', $details['users']) }}
                                                        </div>
                                                    @endif

                                                    @if (count($details['roles']) === 0 && count($details['users']) === 0)
                                                        <span class="text-gray-500">No assignments</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    <button wire:click="deletePermission('{{ $details['name'] }}')"
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <h4 class="font-medium mb-2">Quick Assign</h4>
                                <div class="flex flex-wrap gap-2">
                                    <button wire:click="bulkAssignToRole('{{ $model }}', 'admin')"
                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                        Admin Full Access
                                    </button>
                                    <button wire:click="bulkAssignToRole('{{ $model }}', 'editor')"
                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                        Editor (Read/Update)
                                    </button>
                                    <button wire:click="bulkAssignToRole('{{ $model }}', 'viewer')"
                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                        Viewer (Read Only)
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500">No permissions have been set up yet.</p>
                @endif
            </div>
        @endif

        <!-- Roles Tab -->
        @if ($activeTab === 'roles')
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Manage Roles</h2>
                    <button wire:click="showCreateRoleModal"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Create New Role
                    </button>
                </div>

                @if (count($roles) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role Name</th>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permissions Count</th>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td class="py-2 px-4 border-b border-gray-200">{{ $role['name'] }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            {{ $role['permissionsCount'] ?? 0 }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            <button wire:click="editRole({{ $role['id'] }})"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline mr-2">
                                                Edit
                                            </button>
                                            <button wire:click="deleteRole({{ $role['id'] }})"
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No roles have been created yet.</p>
                @endif
            </div>
        @endif

        <!-- Permission Matrix Tab -->
        @if ($activeTab === 'matrix')
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-xl font-semibold mb-4">Permission Matrix</h2>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Filter by Role
                    </label>
                    <select wire:model="selectedRoleFilter"
                        class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if (count($rules) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Model</th>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Read</th>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Create</th>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Update</th>
                                    <th
                                        class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rules as $model => $actions)
                                    <tr>
                                        <td class="py-2 px-4 border-b border-gray-200 font-medium">
                                            {{ $model }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-center">
                                            @if (isset($actions['read']))
                                                <button wire:click="togglePermission('{{ $model }}', 'read')"
                                                    class="text-green-600 hover:text-green-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="togglePermission('{{ $model }}', 'read')"
                                                    class="text-gray-400 hover:text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-center">
                                            @if (isset($actions['create']))
                                                <button wire:click="togglePermission('{{ $model }}', 'create')"
                                                    class="text-green-600 hover:text-green-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="togglePermission('{{ $model }}', 'create')"
                                                    class="text-gray-400 hover:text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-center">
                                            @if (isset($actions['update']))
                                                <button wire:click="togglePermission('{{ $model }}', 'update')"
                                                    class="text-green-600 hover:text-green-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="togglePermission('{{ $model }}', 'update')"
                                                    class="text-gray-400 hover:text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-center">
                                            @if (isset($actions['delete']))
                                                <button wire:click="togglePermission('{{ $model }}', 'delete')"
                                                    class="text-green-600 hover:text-green-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="togglePermission('{{ $model }}', 'delete')"
                                                    class="text-gray-400 hover:text-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No permissions have been set up yet.</p>
                @endif
            </div>
        @endif

        <!-- Create Role Modal -->
        @if ($showRoleModal)
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-8 max-w-md w-full">
                    <h3 class="text-lg font-medium mb-4">{{ $editingRoleId ? 'Edit Role' : 'Create New Role' }}</h3>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="roleName">
                            Role Name
                        </label>
                        <input wire:model="roleName" id="roleName" type="text"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('roleName')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="closeRoleModal"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                            Cancel
                        </button>
                        <button wire:click="saveRole"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ $editingRoleId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
