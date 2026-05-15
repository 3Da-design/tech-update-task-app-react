<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
  </head>
  <body class="min-h-screen bg-[#FDFDFC] p-6 text-[#1B1B18] antialiased dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
    <div class="max-w-5xl mx-auto">
      @if (session('status'))
        <p class="mb-4 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#161615]">
          {{ session('status') }}
        </p>
      @endif

      <main>
        @yield('content')
      </main>
    </div>
  </body>
</html>