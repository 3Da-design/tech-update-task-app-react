<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="font-sans">
        <div class="app-shell">
            @include('layouts.partials.header')

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
