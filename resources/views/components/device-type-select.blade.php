@props(['name' => 'device_type', 'value' => '', 'required' => false, 'placeholder' => 'Select Device Type'])

@php
$deviceTypes = [
    'node_ai' => 'Node AI',
    'cctv' => 'CCTV',
    'mikrotik' => 'Mikrotik'
];
@endphp

<select name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}>

    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach($deviceTypes as $key => $label)
        <option value="{{ $key }}" {{ $value === $key ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
