<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-rr-bg text-rr-fg antialiased">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="rr-atmosphere">
                <div class="rr-orb rr-orb-1 motion-safe:animate-pulse"></div>
                <div class="rr-orb rr-orb-2"></div>
            </div>

            <header class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-6 sm:px-8">
                <a href="{{ route('home') }}" class="rr-brand-mark text-lg">
                    {{ config('app.name', 'RangRez') }}
                </a>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <x-theme-switcher compact />

                    <nav class="flex items-center gap-3">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-md border border-[var(--rr-border)] bg-[var(--rr-surface)] px-4 py-2 transition hover:brightness-110"
                            >
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-3 py-2 text-rr-muted transition hover:text-rr-fg">
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-md border border-[var(--rr-border)] bg-[var(--rr-surface)] px-4 py-2 transition hover:brightness-110"
                                >
                                    {{ __('Register') }}
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="mx-auto flex min-h-[calc(100vh-5.5rem)] max-w-6xl flex-col justify-center px-6 pb-16 pt-8 sm:px-8 lg:pb-24">
                <div class="max-w-3xl motion-safe:rr-fade-up">
                    <p class="mb-4 text-sm uppercase tracking-[0.35em] text-rr-accent">
                        {{ __('Event operations') }}
                    </p>

                    <h1 class="font-serif text-6xl leading-[0.95] tracking-tight text-[var(--rr-hero-fg)] sm:text-7xl lg:text-8xl">
                        {{ config('app.name', 'RangRez') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-rr-muted sm:text-xl">
                        {{ __('Plan budgets, track expenses and repayments, sell early-bird tickets, and check guests in — all in one place.') }}
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rr-cta rounded-md px-5 py-3 text-sm font-semibold transition"
                            >
                                {{ __('Open dashboard') }}
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rr-cta rounded-md px-5 py-3 text-sm font-semibold transition"
                            >
                                {{ __('Team login') }}
                            </a>
                        @endauth

                        @if ($featuredEvent)
                            <a
                                href="{{ route('public.events.show', $featuredEvent) }}"
                                class="rounded-md border border-[var(--rr-border)] px-5 py-3 text-sm font-medium text-rr-fg transition hover:bg-[var(--rr-surface)]"
                            >
                                {{ __('View :event', ['event' => $featuredEvent->title]) }}
                            </a>
                        @endif
                    </div>
                </div>

                @if ($upcomingEvents->isNotEmpty())
                    <section class="mt-20 motion-safe:rr-fade-up-delayed">
                        <h2 class="mb-4 text-sm uppercase tracking-[0.25em] text-rr-muted">
                            {{ __('Upcoming events') }}
                        </h2>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($upcomingEvents as $event)
                                <a
                                    href="{{ route('public.events.show', $event) }}"
                                    class="group block border-t border-[var(--rr-border)] pt-4 transition hover:border-[var(--rr-accent)]"
                                >
                                    <div class="text-xl font-medium text-rr-fg transition group-hover:text-rr-accent">
                                        {{ $event->title }}
                                    </div>
                                    <p class="mt-2 text-sm text-rr-muted">
                                        {{ $event->starts_at->format('M j, Y · g:i A') }}
                                        @if ($event->venue)
                                            · {{ $event->venue }}
                                        @endif
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>
        </div>

        @fluxScripts
    </body>
</html>
