<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="relative isolate min-h-screen overflow-x-hidden bg-rr-bg text-rr-fg antialiased">
        <div class="rr-atmosphere">
            <div class="rr-orb rr-orb-1 opacity-70"></div>
            <div class="rr-orb rr-orb-2 opacity-60"></div>
        </div>

        <header class="border-b border-[var(--rr-border)] bg-[var(--rr-surface)] backdrop-blur-md">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="rr-brand-mark text-lg" wire:navigate>
                    {{ config('app.name', 'RangRez') }}
                </a>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <x-theme-switcher compact />

                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-rr-muted transition hover:text-rr-fg" wire:navigate>
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-rr-muted transition hover:text-rr-fg">
                                {{ __('Log in') }}
                            </a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        <main class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
