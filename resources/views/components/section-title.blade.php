@props(['description' => null])

<header class="mb-6">
  <h2 class="text-lg font-semibold text-gray-900">
    {{ $slot }}
  </h2>
  @if ($description)
    <p class="mt-1 text-sm text-gray-600">
      {{ $description }}
    </p>
  @endif
</header>
