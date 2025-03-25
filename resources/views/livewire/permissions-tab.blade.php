<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-3">Permission Management</h2>

    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mb-6">
        <div class="md:w-1/3">
            <label for="role-select" class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
            <div class="relative">
                <select id="role-select"
                    class="w-full p-2.5 rounded-md border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 appearance-none"
                    wire:model="selectedRole" wire:change="loadPermissions">
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <span class="text-xs">▼</span>
                </div>
            </div>
        </div>

        <div class="md:w-1/3">
            <label for="model-filter" class="block text-sm font-medium text-gray-700 mb-2">Filter Models</label>
            <div class="relative">
                <input id="model-filter" type="text" placeholder="Search models..."
                    wire:model.live.debounce.300ms="modelFilter"
                    class="w-full p-2.5 pl-8 rounded-md border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-gray-500">
                    🔍
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($permissions as $model => $actions)
            <div
                class="bg-white shadow-sm rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow duration-300">
                <h3 class="text-lg font-medium mb-3 pb-2 border-b text-gray-800">{{ $model }}</h3>

                <div class="space-y-2">
                    @foreach ($actions as $action => $hasPermission)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <span class="text-gray-700">{{ $action }}</span>
                            <button wire:click="togglePermission('{{ $model }}', '{{ $action }}')"
                                class="{{ $hasPermission ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }} 
                                flex items-center justify-center rounded-md px-3 py-1.5 transition-colors duration-200">
                                @if ($hasPermission)
                                    <span class="mr-1">✓</span>
                                    <span>Enabled</span>
                                @else
                                    <span class="mr-1">✗</span>
                                    <span>Disabled</span>
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button wire:click="showAttributesModal('{{ $model }}')"
                        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 w-full transition-colors duration-200 font-medium">
                        Attributes Permissions
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        @if ($attributesModalVisible)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[80vh] overflow-y-auto">
                    <div class="px-6 py-4 bg-blue-500 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white">Attributes for {{ $selectedModel }}</h2>
                        <button wire:click="closeAttributesModal"
                            class="text-white focus:outline-none transition-colors duration-200 hover:text-gray-100 text-xl">
                            ✕
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr>
                                        <th
                                            class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                            Attribute
                                        </th>
                                        @php
                                            // Get unique action types across all attributes
                                            $actionTypes = [];
                                            foreach ($selectedModelAttributes as $attribute => $actions) {
                                                foreach ($actions as $action => $hasPermission) {
                                                    if (!in_array($action, $actionTypes)) {
                                                        $actionTypes[] = $action;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @foreach ($actionTypes as $actionType)
                                            <th
                                                class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ $actionType }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedModelAttributes as $attribute => $actions)
                                        <tr class="hover:bg-gray-50">
                                            <td
                                                class="py-2 px-4 border-b border-gray-200 text-sm font-medium text-gray-900">
                                                {{ $attribute }}
                                            </td>

                                            @foreach ($actionTypes as $actionType)
                                                <td class="py-2 px-4 border-b border-gray-200 text-center">
                                                    @if (isset($actions[$actionType]))
                                                        <button
                                                            wire:click="toggleAttributePermission('{{ $actionType }}', '{{ $attribute }}')"
                                                            class="{{ $actions[$actionType] ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }} 
                                                            inline-flex items-center justify-center rounded-md px-3 py-1 transition-colors duration-200">
                                                            @if ($actions[$actionType])
                                                                <span>✓</span>
                                                            @else
                                                                <span>✗</span>
                                                            @endif
                                                        </button>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t flex justify-end">
                        <button wire:click="closeAttributesModal"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors duration-200 mr-2">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
