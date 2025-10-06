@props([
    'headers' => [],
    'hoverable' => true,
    'striped' => false,
])

<div class="overflow-x-auto rounded-xl">
  <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }}>
    @if (!empty($headers))
      <thead class="bg-gray-100">
        <tr>
          @foreach ($headers as $header)
            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
              {{ $header }}
            </th>
          @endforeach
        </tr>
      </thead>
    @endif

    <tbody class="bg-white divide-y divide-gray-100">
      {{ $slot }}
    </tbody>
  </table>
</div>
