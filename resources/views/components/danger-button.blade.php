<button {{ $attributes->merge(['type' => 'submit', 'class' => 'app-btn--danger disabled:opacity-50']) }}>
  {{ $slot }}
</button>
