<button {{ $attributes->merge(['type' => 'button', 'class' => 'app-btn--secondary disabled:opacity-50']) }}>
  {{ $slot }}
</button>
