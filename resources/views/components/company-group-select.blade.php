@props(['name' => 'group_id', 'value' => '', 'required' => false, 'placeholder' => 'Select Company Group'])

<select name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}>

    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach(App\Models\CompanyGroup::active()->orderBy('group_name')->get() as $group)
        <option value="{{ $group->id }}" {{ $value == $group->id ? 'selected' : '' }}>
            {{ $group->group_name }} ({{ $group->province_name }})
        </option>
    @endforeach
</select>
