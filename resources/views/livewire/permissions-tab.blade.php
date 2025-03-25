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

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        class="py-3 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                        Model
                    </th>

                    @php
                        // Get all possible action types across all models
                        $allActionTypes = [];
                        foreach ($permissions as $model => $modelActions) {
                            foreach ($modelActions as $action => $hasPermission) {
                                if (!in_array($action, $allActionTypes)) {
                                    $allActionTypes[] = $action;
                                }
                            }
                        }
                        sort($allActionTypes);
                    @endphp

                    @foreach ($allActionTypes as $actionType)
                        <th
                            class="py-3 px-4 border-b border-gray-200 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $actionType }}
                        </th>
                    @endforeach

                    <th
                        class="py-3 px-4 border-b border-gray-200 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                        Attributes
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($permissions as $model => $actions)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900">
                            {{ $model }}
                        </td>

                        @foreach ($allActionTypes as $actionType)
                            <td class="py-3 px-4 text-center">
                                @if (isset($actions[$actionType]))
                                    <button wire:click="togglePermission('{{ $model }}', '{{ $actionType }}')"
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

                        <td class="py-3 px-4 text-center">
                            <button wire:click="showAttributesModal('{{ $model }}')"
                                class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition-colors duration-200 text-sm">
                                Attributes
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        @if ($attributesModalVisible)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl flex flex-col" style="max-height: 85vh;">
                    <!-- Fixed Header -->
                    <div class="px-6 py-4 bg-blue-500 flex items-center justify-between sticky top-0 z-10">
                        <h2 class="text-xl font-bold text-white">Attributes for {{ $selectedModel }}</h2>
                        <button wire:click="closeAttributesModal"
                            class="text-white focus:outline-none transition-colors duration-200 hover:text-gray-100 text-xl">
                            ✕
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="overflow-y-auto flex-grow p-6" style="max-height: calc(85vh - 140px);">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="sticky top-0 bg-white z-10">
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

                    <!-- Fixed Footer -->
                    <div class="bg-gray-50 px-6 py-4 border-t flex justify-end sticky bottom-0">
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
