<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-rr-bg text-rr-fg antialiased">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col overflow-hidden p-10 text-[var(--rr-hero-fg)] lg:flex">
                <div class="rr-atmosphere !z-0">
                    <div class="rr-orb rr-orb-1"></div>
                    <div class="rr-orb rr-orb-2"></div>
                </div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-2 text-lg font-medium" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-md bg-[var(--rr-accent)] text-[var(--rr-accent-fg)]">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </span>
                    <span class="rr-brand-mark">{{ config('app.name', 'RangRez') }}</span>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="relative w-full lg:p-8">
                <div class="absolute end-4 top-4 z-20 sm:end-6 sm:top-6">
                    <x-theme-switcher compact />
                </div>

                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex size-9 items-center justify-center rounded-md bg-[var(--rr-accent)] text-[var(--rr-accent-fg)]">
                            <x-app-logo-icon class="size-5 fill-current" />
                        </span>
                        <span class="rr-brand-mark text-sm">{{ config('app.name', 'RangRez') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
