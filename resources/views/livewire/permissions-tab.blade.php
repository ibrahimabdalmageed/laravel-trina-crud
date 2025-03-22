<div>
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
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            <button wire:click="deletePermission('{{ $details['name'] }}')"
                                                class="text-red-600 hover:text-red-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-gray-500">No permissions have been set up yet.</p>
        @endif
    </div>
</div>
