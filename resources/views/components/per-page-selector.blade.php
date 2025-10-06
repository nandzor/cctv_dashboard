@props([
  'options' => [10 => '10', 20 => '20', 50 => '50', 100 => '100'],
  'current' => 10,
  'url' => null,
  'param' => 'per_page'
])

<div x-data="{ 
  selected: {{ $current }}, 
  open: false,
  changePerPage(value) {
    this.selected = value;
    this.open = false;
    
    // Update URL with new per_page parameter
    const url = new URL(window.location);
    url.searchParams.set('{{ $param }}', value);
    
    // Preserve other search parameters
    @if(request()->has('search'))
      url.searchParams.set('search', '{{ request()->get('search') }}');
    @endif
    
    window.location.href = url.toString();
  }
}" class="relative inline-block text-left">
  
  <div>
    <button 
      @click="open = !open" 
      @click.away="open = false"
      type="button" 
      class="inline-flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
    >
      <span x-text="selected + ' per page'"></span>
      <svg class="w-4 h-4 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
  </div>

  <div 
    x-show="open" 
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="transform opacity-0 scale-95"
    x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
    style="display: none;"
  >
    <div class="py-1">
      @foreach($options as $value => $label)
        <button
          @click="changePerPage({{ $value }})"
          type="button"
          class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ $value == $current ? 'bg-blue-50 text-blue-700' : '' }}"
        >
          <div class="flex items-center justify-between">
            <span>{{ $label }}</span>
            @if($value == $current)
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            @endif
          </div>
        </button>
      @endforeach
    </div>
  </div>
</div>
