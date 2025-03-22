<div>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-semibold mb-6">Manage User Roles</h2>

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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="selectedUserForRoles">
                        Select User
                    </label>
                    <select wire:model="selectedUserForRoles" wire:change="updateSelectedUser" id="selectedUserForRoles"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select a user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user['id'] }}">{{ $user['name'] }} ({{ $user['email'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="selectedRoleToAssign">
                        Select Role to Assign
                    </label>
                    <div class="flex">
                        <select wire:model="selectedRoleToAssign" id="selectedRoleToAssign"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select a role</option>
                            @foreach ($allRoles as $role)
                                <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                            @endforeach
                        </select>
                        <button wire:click="assignRoleToSelectedUser"
                            class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Assign
                        </button>
                    </div>
                </div>
            </div>

            @if (!empty($selectedUserForRoles))
                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Current Roles</h3>

                    @if (count($userCurrentRoles) > 0)
                        <div class="space-y-2">
                            @foreach ($userCurrentRoles as $role)
                                <div class="flex items-center justify-between p-2 border rounded">
                                    <span>{{ $role['name'] }}</span>
                                    <button wire:click="removeRoleFromUser({{ $role['id'] }})"
                                        class="text-red-600 hover:text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">This user has no roles assigned.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>
