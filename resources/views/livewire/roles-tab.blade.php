<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Roles Management</h2>
        <p class="text-gray-600">Create and manage user roles for your application</p>
    </div>

    <button
        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md mb-6 transition duration-200 shadow-sm"
        wire:click="showCreateRoleModal">
        👑
        Create New Role
    </button>

    <!-- Modal for creating roles -->
    @if ($openCreateRoleModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 transition-opacity duration-300"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Create New Role</h3>
                    <button class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                        wire:click="hideCreateRoleModal">
                        ❌
                    </button>
                </div>

                <div class="mb-4">
                    <label for="role-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role
                        Name</label>
                    <input type="text" id="role-name"
                        class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        wire:model="createRoleName" placeholder="Enter role name">
                </div>

                <div class="flex justify-end gap-3 mt-5">
                    <button
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition duration-200"
                        wire:click="hideCreateRoleModal">
                        Cancel
                    </button>
                    <button
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition duration-200 flex items-center gap-1"
                        wire:click="createRole" wire:loading.attr="disabled" wire:loading.class="opacity-75">
                        <span wire:loading.remove wire:target="createRole">Create</span>
                        <span wire:loading wire:target="createRole">Creating...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Role cards grid -->
    @if (count($roles) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($roles as $role)
                <div
                    class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3">
                                👑
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $role }}</h3>
                        </div>
                        <button class="text-red-500 hover:text-red-700 transition duration-200"
                            @click="if (confirm('Are you sure you want to delete the {{ $role }} role?')) { $wire.deleteRole('{{ $role }}') }">
                            ❌
                        </button>
                    </div>
                    <div class="mt-2">
                        <button
                            class="w-full text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium text-left flex items-center gap-1 transition duration-200">
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div
            class="flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
            ❕
            <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">No roles found</p>
            <p class="text-gray-500 dark:text-gray-500 text-sm mt-1">Create your first role to get started</p>
        </div>
    @endif
</div>
