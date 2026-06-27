@props(['status'])

@if ($status)
  <x-flash-message {{ $attributes }}>
    {{ $status }}
  </x-flash-message>
@endif
