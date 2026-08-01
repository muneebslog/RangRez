@props([
    'compact' => false,
])

@php
    $themes = [
        'splash' => ['label' => __('Splash'), 'swatches' => ['#d9468f', '#0d9488', '#0e0a10']],
        'festival' => ['label' => __('Festival'), 'swatches' => ['#e07a1a', '#c0266b', '#140e08']],
        'ink' => ['label' => __('Ink'), 'swatches' => ['#2f3f8f', '#c9a227', '#0a0c14']],
    ];
@endphp

<div
    x-data="{
        theme: window.RangRez?.currentTheme?.() || 'splash',
        setTheme(id) {
            this.theme = window.RangRez.applyTheme(id);
        },
    }"
    @rangrez-theme-changed.window="theme = $event.detail.theme"
    {{ $attributes->class([$compact ? 'flex items-center gap-1' : 'grid gap-3']) }}
>
    @unless ($compact)
        <div class="text-sm font-medium text-rr-fg">{{ __('Color theme') }}</div>
        <div class="grid gap-2 sm:grid-cols-3">
            @foreach ($themes as $id => $meta)
                <button
                    type="button"
                    @click="setTheme(@js($id))"
                    :aria-pressed="theme === @js($id)"
                    :class="theme === @js($id)
                        ? 'border-[var(--rr-accent)] ring-2 ring-[var(--rr-accent)]/40'
                        : 'border-[var(--rr-border)] hover:border-[var(--rr-accent)]/50'"
                    class="flex flex-col gap-3 rounded-xl border bg-[var(--rr-surface)] p-3 text-start transition"
                >
                    <div class="flex gap-1.5">
                        @foreach ($meta['swatches'] as $swatch)
                            <span class="size-4 rounded-full" style="background: {{ $swatch }}"></span>
                        @endforeach
                    </div>
                    <span class="text-sm font-medium text-rr-fg">{{ $meta['label'] }}</span>
                </button>
            @endforeach
        </div>
    @else
        <div class="flex items-center gap-1 rounded-md border border-[var(--rr-border)] bg-[var(--rr-surface)] p-0.5" role="group" aria-label="{{ __('Color theme') }}">
            @foreach ($themes as $id => $meta)
                <button
                    type="button"
                    @click="setTheme(@js($id))"
                    :aria-pressed="theme === @js($id)"
                    :title="@js($meta['label'])"
                    :class="theme === @js($id) ? 'bg-[var(--rr-accent)] text-[var(--rr-accent-fg)]' : 'text-[var(--rr-muted)] hover:text-[var(--rr-fg)]'"
                    class="rounded px-2 py-1 text-[11px] font-medium tracking-wide uppercase transition"
                >
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>
    @endunless
</div>
