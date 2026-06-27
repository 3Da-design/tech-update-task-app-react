<button {{ $attributes->merge(['type' => 'submit', 'class' => 'app-btn--primary disabled:opacity-50']) }}>
  {{ $slot }}
</button>
