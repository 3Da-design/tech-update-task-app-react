@props(['title'])

<div {{ $attributes->merge(['class' => 'app-page-heading']) }}>
    <h1 class="app-page-title">{{ $title }}</h1>
    @isset($actions)
        <div class="flex shrink-0 items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
