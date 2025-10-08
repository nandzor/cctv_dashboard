@props([
    'items' => [],
    'perPageOptions' => [5, 10, 20, 50],
    'defaultPerPage' => 10,
    'showPerPageSelector' => true,
    'showPaginationInfo' => true,
    'maxVisiblePages' => 5,
    'itemName' => 'items',
    'emptyMessage' => 'No items found',
    'scrollToTop' => true,
    'storageKey' => 'client_pagination_per_page'
])

<div x-data="clientPagination({
    items: @js($items),
    perPageOptions: @js($perPageOptions),
    defaultPerPage: {{ $defaultPerPage }},
    maxVisiblePages: {{ $maxVisiblePages }},
    itemName: '{{ $itemName }}',
    emptyMessage: '{{ $emptyMessage }}',
    scrollToTop: {{ $scrollToTop ? 'true' : 'false' }},
    storageKey: '{{ $storageKey }}'
})">
    <!-- Pagination Controls - Top -->
    @if($showPerPageSelector || $showPaginationInfo)
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                @if($showPerPageSelector)
                    <!-- Items per page selector -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select
                            x-model="perPage"
                            @change="goToPage(1)"
                            class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }} per page</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($showPaginationInfo)
                    <!-- Pagination info -->
                    <div class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium" x-text="startItem"></span>
                        to
                        <span class="font-medium" x-text="endItem"></span>
                        of
                        <span class="font-medium" x-text="totalItems"></span>
                        <span x-text="itemName"></span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Content Slot -->
    <div>
        {{ $slot }}
    </div>

    <!-- Pagination Controls - Bottom -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            @if($showPaginationInfo)
                <!-- Pagination info -->
                <div class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium" x-text="startItem"></span>
                    to
                    <span class="font-medium" x-text="endItem"></span>
                    of
                    <span class="font-medium" x-text="totalItems"></span>
                    <span x-text="itemName"></span>
                </div>
            @endif

            <!-- Pagination buttons -->
            <div class="flex items-center space-x-2" x-show="totalPages > 1">
                <!-- Previous button -->
                <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>

                <!-- Page numbers -->
                <template x-for="page in visiblePages" :key="page">
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        :class="page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 bg-white hover:bg-gray-50'"
                        @click="goToPage(page)"
                    >
                        <span x-text="page"></span>
                    </button>
                </template>

                <!-- Next button -->
                <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    Next
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
