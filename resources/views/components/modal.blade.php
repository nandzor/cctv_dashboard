@props(['id', 'title' => '', 'size' => 'md', 'footer' => true])

@php
  $sizes = [
      'sm' => 'max-w-md',
      'md' => 'max-w-2xl',
      'lg' => 'max-w-4xl',
      'xl' => 'max-w-6xl',
  ];
@endphp

<div x-data="{ show: false }" x-show="show" x-on:open-modal-{{ $id }}.window="show = true"
  x-on:close-modal-{{ $id }}.window="show = false" x-on:keydown.escape.window="show = false" x-cloak
  class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
  <!-- Backdrop -->
  <div x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="show = false"></div>

  <!-- Modal -->
  <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
    <div x-show="show" x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
      x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
      x-transition:leave-end="opacity-0 transform scale-95"
      class="inline-block w-full {{ $sizes[$size] }} p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
      @click.away="show = false">
      <!-- Header -->
      @if ($title)
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-2xl font-bold text-gray-900">{{ $title }}</h3>
          <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      @endif

      <!-- Content -->
      <div class="mt-4">
        {{ $slot }}
      </div>

      <!-- Footer -->
      @if ($footer)
        <div class="mt-6 flex items-center justify-end space-x-3">
          {{ $footer }}
        </div>
      @endif
    </div>
  </div>
</div>
