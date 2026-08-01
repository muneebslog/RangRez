<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="relative isolate min-h-screen overflow-x-hidden bg-rr-bg text-rr-fg antialiased">
        <div class="rr-atmosphere">
            <div class="rr-orb rr-orb-1"></div>
            <div class="rr-orb rr-orb-2"></div>
        </div>

        <div class="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="absolute end-4 top-4 sm:end-6 sm:top-6">
                <x-theme-switcher compact />
            </div>

            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex size-9 items-center justify-center rounded-md bg-[var(--rr-accent)] text-[var(--rr-accent-fg)]">
                        <x-app-logo-icon class="size-5 fill-current" />
                    </span>
                    <span class="rr-brand-mark text-sm">{{ config('app.name', 'RangRez') }}</span>
                </a>

                <div class="rr-surface rounded-xl text-rr-fg shadow-xs">
                    <div class="px-10 py-8">{{ $slot }}</div>
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
