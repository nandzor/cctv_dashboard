@props(['padding' => true, 'shadow' => true])

<div
  {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 overflow-hidden ' . ($shadow ? 'shadow-sm hover:shadow-md transition-shadow duration-300' : '') . ($padding ? ' p-6' : '')]) }}>
  {{ $slot }}
</div>
