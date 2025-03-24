<div>
    <h2 class="text-xl font-semibold mb-4">Roles</h2>
    <button class="bg-blue-500 text-white px-4 py-2 rounded-md mb-4" wire:click="showCreateRoleModal">
        Create Role
    </button>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if ($openCreateRoleModal)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Create Role</h3>
                    <input type="text" class="w-full p-2 rounded-md border border-gray-300" wire:model="createRoleName">
                    <div class="flex justify-end gap-2 mt-4">
                        <button class="bg-blue-500 text-white px-4 py-2 rounded-md" wire:click="createRole">
                            Create
                        </button>
                        <button class="bg-red-500 text-white px-4 py-2 rounded-md" wire:click="hideCreateRoleModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @foreach ($roles as $role)
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2">{{ $role }}</h3>
                <button class="bg-red-500 text-white px-4 py-2 rounded-md"
                    @click="if (confirm('Are you sure?')) { $wire.deleteRole('{{ $role }}') }">
                    Delete
                </button>
            </div>
        @endforeach
    </div>
</div>
