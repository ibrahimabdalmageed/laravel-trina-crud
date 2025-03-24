<div>
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">User Roles Management</h2>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="user-select" class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                <select id="user-select"
                    class="w-full p-2.5 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                    wire:model="selectedUser" wire:change="getCurrentRoles">
                    <option value="">-- Select a user --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="role-select" class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
                <select id="role-select"
                    class="w-full p-2.5 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                    wire:model="selectedRole" wire:change="getCurrentRoles">
                    <option value="">-- Select a role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-md transition duration-300 ease-in-out flex items-center justify-center"
                    wire:click="assignRoleToUser" wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Assign Role
                    <span wire:loading wire:target="assignRoleToUser" class="ml-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Current Roles</h3>

        @if (count($currentRoles) > 0)
            <div class="space-y-3">
                @foreach ($currentRoles as $role)
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <span
                            class="font-medium text-gray-700 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">{{ $role }}</span>
                        <button
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md transition duration-300 ease-in-out flex items-center"
                            @click="if (confirm('Are you sure you want to remove this role?')) { $wire.removeRoleForUser('{{ $role }}') }">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p>No roles assigned to this user yet.</p>
            </div>
        @endif
    </div>
</div>
