<div>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-semibold mb-4">Add New Permission</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="bg-green-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline">{{ session('info') }}</span>
            </div>
        @endif

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
                    <select wire:model="selectedUserId" wire:change="$refresh"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select a role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                        @endforeach
                    </select>
                @else
                    <select wire:model="selectedUserId" wire:change="$refresh"
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

            <div class="flex items-end space-x-2">
                <button wire:click="addPermission"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Permission
                </button>

                @if ($isRole && !empty($selectedUserId))
                    <button wire:loading.attr="disabled" wire:loading.class="bg-green-500 opacity-50 cursor-not-allowed"
                        wire:click="syncPermissions"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Sync All Models
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-semibold mb-4">Current Permissions</h2>

        <!-- Filter Controls -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Filter by Role
                </label>
                <select wire:model="filterByRoleId" wire:change="$refresh"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Filter by User
                </label>
                <select wire:model="filterByUserId" wire:change="$refresh"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="resetFilters"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Reset Filters
                </button>
            </div>
        </div>

        @if (count($filteredRules) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th
                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Model
                            </th>
                            <th
                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role/User
                            </th>
                            <th
                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Read
                            </th>
                            <th
                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Create
                            </th>
                            <th
                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Update
                            </th>
                            <th
                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Delete
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredRules as $model => $actions)
                            @php
                                // Get all unique roles and users for this model
                                $allRolesUsers = [];

                                // If filtering by role, only show that role
                                if (!empty($filterByRoleId)) {
                                    $role = collect($roles)->firstWhere('id', $filterByRoleId);
                                    if ($role) {
                                        $allRolesUsers[$role['name']] = [
                                            'type' => 'role',
                                            'name' => $role['name'],
                                            'id' => $role['id'],
                                        ];
                                    }
                                }
                                // If filtering by user, only show that user
                                elseif (!empty($filterByUserId)) {
                                    $user = collect($users)->firstWhere('id', $filterByUserId);
                                    if ($user) {
                                        $allRolesUsers[$user['name']] = [
                                            'type' => 'user',
                                            'name' => $user['name'],
                                            'id' => $user['id'],
                                        ];
                                    }
                                }
                                // Otherwise, get all roles and users for this model
                                else {
                                    foreach ($actions as $actionDetails) {
                                        foreach ($actionDetails['roles'] as $roleName) {
                                            $role = collect($roles)->firstWhere('name', $roleName);
                                            if ($role && !isset($allRolesUsers[$roleName])) {
                                                $allRolesUsers[$roleName] = [
                                                    'type' => 'role',
                                                    'name' => $roleName,
                                                    'id' => $role['id'],
                                                ];
                                            }
                                        }
                                        foreach ($actionDetails['users'] as $userName) {
                                            $user = collect($users)->firstWhere('name', $userName);
                                            if ($user && !isset($allRolesUsers[$userName])) {
                                                $allRolesUsers[$userName] = [
                                                    'type' => 'user',
                                                    'name' => $userName,
                                                    'id' => $user['id'],
                                                ];
                                            }
                                        }
                                    }
                                }
                            @endphp

                            @foreach ($allRolesUsers as $roleUser)
                                <tr>
                                    @if ($loop->first)
                                        <td class="py-2 px-4 border-b border-gray-200 font-medium"
                                            rowspan="{{ count($allRolesUsers) }}">
                                            {{ $model }}
                                        </td>
                                    @endif
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <span
                                            class="{{ $roleUser['type'] === 'role' ? 'text-blue-600' : 'text-green-600' }}">
                                            {{ $roleUser['name'] }}
                                            <span
                                                class="text-xs text-gray-500">({{ ucfirst($roleUser['type']) }})</span>
                                        </span>
                                    </td>

                                    <!-- Read Permission -->
                                    <td class="py-2 px-4 border-b border-gray-200 text-center">
                                        @php
                                            $hasPermission =
                                                isset($actions['read']) &&
                                                (($roleUser['type'] === 'role' &&
                                                    in_array($roleUser['name'], $actions['read']['roles'])) ||
                                                    ($roleUser['type'] === 'user' &&
                                                        in_array($roleUser['name'], $actions['read']['users'])));
                                        @endphp

                                        <button
                                            wire:click="togglePermission('{{ $model }}', 'read', {{ $roleUser['id'] }}, {{ $roleUser['type'] === 'role' ? 1 : 0 }})"
                                            class="{{ $hasPermission ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800' }}">
                                            @if ($hasPermission)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </button>
                                    </td>

                                    <!-- Create Permission -->
                                    <td class="py-2 px-4 border-b border-gray-200 text-center">
                                        @php
                                            $hasPermission =
                                                isset($actions['create']) &&
                                                (($roleUser['type'] === 'role' &&
                                                    in_array($roleUser['name'], $actions['create']['roles'])) ||
                                                    ($roleUser['type'] === 'user' &&
                                                        in_array($roleUser['name'], $actions['create']['users'])));
                                        @endphp

                                        <button
                                            wire:click="togglePermission('{{ $model }}', 'create', {{ $roleUser['id'] }}, {{ $roleUser['type'] === 'role' ? 1 : 0 }})"
                                            class="{{ $hasPermission ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800' }}">
                                            @if ($hasPermission)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </button>
                                    </td>

                                    <!-- Update Permission -->
                                    <td class="py-2 px-4 border-b border-gray-200 text-center">
                                        @php
                                            $hasPermission =
                                                isset($actions['update']) &&
                                                (($roleUser['type'] === 'role' &&
                                                    in_array($roleUser['name'], $actions['update']['roles'])) ||
                                                    ($roleUser['type'] === 'user' &&
                                                        in_array($roleUser['name'], $actions['update']['users'])));
                                        @endphp

                                        <button
                                            wire:click="togglePermission('{{ $model }}', 'update', {{ $roleUser['id'] }}, {{ $roleUser['type'] === 'role' ? 1 : 0 }})"
                                            class="{{ $hasPermission ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800' }}">
                                            @if ($hasPermission)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </button>
                                    </td>

                                    <!-- Delete Permission -->
                                    <td class="py-2 px-4 border-b border-gray-200 text-center">
                                        @php
                                            $hasPermission =
                                                isset($actions['delete']) &&
                                                (($roleUser['type'] === 'role' &&
                                                    in_array($roleUser['name'], $actions['delete']['roles'])) ||
                                                    ($roleUser['type'] === 'user' &&
                                                        in_array($roleUser['name'], $actions['delete']['users'])));
                                        @endphp

                                        <button
                                            wire:click="togglePermission('{{ $model }}', 'delete', {{ $roleUser['id'] }}, {{ $roleUser['type'] === 'role' ? 1 : 0 }})"
                                            class="{{ $hasPermission ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800' }}">
                                            @if ($hasPermission)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-gray-100 p-4 rounded text-center">
                No permissions found. Add some permissions using the form above.
            </div>
        @endif
    </div>
</div>
