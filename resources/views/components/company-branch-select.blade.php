@props(['name' => 'branch_id', 'value' => '', 'required' => false, 'placeholder' => 'Select Branch'])

<select name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}>

    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach(App\Models\CompanyBranch::active()->with('group')->orderBy('branch_name')->get() as $branch)
        <option value="{{ $branch->id }}" {{ $value == $branch->id ? 'selected' : '' }}>
            {{ $branch->branch_name }} ({{ $branch->city }})
        </option>
    @endforeach
</select>
