<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    @include('layouts.partials.head')
  </head>
  <body class="font-sans">
    <div class="app-shell">
      <main class="app-guest-wrap">
        <div class="app-guest-panel">
          @if (! empty($header))
            <h1 class="app-guest-title">{{ $header }}</h1>
          @endif

          <x-card>
            {{ $slot }}
          </x-card>
        </div>
      </main>
    </div>
  </body>
</html>
