@props(['active'])

@php
  $classes = ($active ?? false)
    ? 'inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-900 transition focus:outline-none'
    : 'inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>
