@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('users.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search users..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            <button type="submit"
              class="px-6 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
              Search
            </button>
          </form>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Show:</span>
            <x-per-page-selector :options="$perPageOptions" :current="$perPage ?? 10" :url="route('users.index')" />
          </div>

          <!-- Add User Button -->
          <x-button variant="primary" size="sm" :href="route('users.create')">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add User
          </x-button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <x-table :headers="['Name', 'Email', 'Role', 'Created', 'Actions']">
      @forelse($users as $user)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div
                  class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md">
                  <span class="text-sm font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $user->email }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$user->isAdmin() ? 'purple' : 'primary'">
              {{ ucfirst($user->role) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $user->created_at->format('M d, Y') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('users.show', $user->id)">
                👁️ View Details
              </x-dropdown-link>

              <x-dropdown-link :href="route('users.edit', $user->id)">
                ✏️ Edit User
              </x-dropdown-link>

              @if ($user->id !== auth()->id())
                <x-dropdown-divider />

                <x-dropdown-button type="button" onclick="confirmDelete({{ $user->id }})" variant="danger">
                  🗑️ Delete User
                </x-dropdown-button>

                <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST"
                  class="hidden">
                  @csrf
                  @method('DELETE')
                </form>
              @endif
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No users found</p>
              <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
            </div>
          </td>
        </tr>
      @endforelse
    </x-table>

    <!-- Pagination Info & Controls -->
    <div class="px-6 py-4 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $users->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $users->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($users->hasPages())
          <x-pagination :paginator="$users" />
        @endif
      </div>
    </div>
  </x-card>

  <!-- Delete Modal -->
  <div x-data="{ show: false, userId: null }" x-show="show"
    x-on:open-modal-confirm-delete.window="show = true; userId = $event.detail.userId"
    x-on:close-modal-confirm-delete.window="show = false" x-on:keydown.escape.window="show = false" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    <!-- Backdrop -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="show = false">
    </div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
      <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
        @click.away="show = false">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-2xl font-bold text-gray-900">Confirm Delete</h3>
          <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="text-center py-4">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Are you sure?</h3>
          <p class="text-sm text-gray-500">This action cannot be undone. The user will be permanently deleted.</p>
        </div>

        <!-- Footer -->
        <div class="mt-6 flex items-center justify-end space-x-3">
          <x-button variant="secondary" @click="show = false">
            Cancel
          </x-button>
          <x-button variant="danger" @click="deleteUser(userId)">
            Delete User
          </x-button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function confirmDelete(userId) {
      // Dispatch event with userId data
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          userId: userId
        }
      }));
    }

    function deleteUser(userId) {
      const form = document.getElementById('delete-form-' + userId);
      if (form) {
        form.submit();
      }
    }

    // Make functions globally available
    window.confirmDelete = confirmDelete;
    window.deleteUser = deleteUser;
  </script>
@endpush
