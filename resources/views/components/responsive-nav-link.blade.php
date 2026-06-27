@props(['active'])

@php
  $classes = ($active ?? false)
    ? 'block w-full border-l-4 border-gray-800 bg-gray-50 py-2 ps-3 pe-4 text-start text-base font-medium text-gray-900 transition duration-150 ease-in-out focus:border-gray-900 focus:bg-gray-100 focus:text-gray-900 focus:outline-none'
    : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>
