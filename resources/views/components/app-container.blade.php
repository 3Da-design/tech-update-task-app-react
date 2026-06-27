@props(['narrow' => false])

<div {{ $attributes->merge(['class' => $narrow ? 'app-main app-main--narrow' : 'app-main']) }}>
  {{ $slot }}
</div>
