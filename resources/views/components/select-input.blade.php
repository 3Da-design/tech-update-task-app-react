@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'app-input']) }}>
  {{ $slot }}
</select>
