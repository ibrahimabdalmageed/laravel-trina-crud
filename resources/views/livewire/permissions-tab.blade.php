<div>
    <h2 class="text-xl font-semibold mb-4">Permissions</h2>

    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mb-6">
        <div class="md:w-1/3">
            <label for="role-select" class="block text-sm font-medium text-gray-700 mb-1">Select Role</label>
            <select id="role-select"
                class="w-full p-2 rounded-md border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                wire:model="selectedRole" wire:change="loadPermissions">
                <option value="">Select Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:w-1/3">
            <label for="model-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter Models</label>
            <div class="relative">
                <input id="model-filter" type="text" placeholder="Search models..."
                    wire:model.live.debounce.300ms="modelFilter"
                    class="w-full p-2 pl-8 rounded-md border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    🔍
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($permissions as $model => $actions)
            <div class="bg-white shadow-md rounded-lg p-4 border border-gray-200">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">{{ $model }}</h3>

                <div class="space-y-2">
                    @foreach ($actions as $action => $hasPermission)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <span class="text-gray-700">{{ $action }}</span>
                            <button wire:click="togglePermission('{{ $model }}', '{{ $action }}')"
                                class="{{ $hasPermission ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }} 
                                        flex items-center justify-center rounded-md px-3 py-1 transition-colors duration-200">
                                @if ($hasPermission)
                                    ✅
                                    <span>Enabled</span>
                                @else
                                    ❌
                                    <span>Disabled</span>
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
