<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0f1412] text-[#f4efe6] antialiased">
        <div class="relative isolate min-h-screen overflow-hidden">
            {{-- Atmospheric full-bleed plane --}}
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_20%_10%,rgba(232,140,48,0.28),transparent_45%),radial-gradient(ellipse_at_85%_20%,rgba(34,120,110,0.35),transparent_40%),linear-gradient(160deg,#0f1412_0%,#1a221c_55%,#0c100e_100%)]"></div>
                <div class="absolute inset-0 opacity-[0.12] [background-image:radial-gradient(rgba(244,239,230,0.45)_1px,transparent_1px)] [background-size:28px_28px]"></div>
                <div class="absolute -right-24 top-10 h-[28rem] w-[28rem] rounded-full bg-[#e88c30]/20 blur-3xl motion-safe:animate-pulse"></div>
                <div class="absolute -left-16 bottom-0 h-[22rem] w-[22rem] rounded-full bg-[#22786e]/25 blur-3xl"></div>
            </div>

            <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6 sm:px-8">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-[0.08em]">
                    {{ config('app.name', 'RangRez') }}
                </a>

                <nav class="flex items-center gap-3 text-sm">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-md border border-white/15 bg-white/5 px-4 py-2 transition hover:bg-white/10"
                        >
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-[#f4efe6]/80 transition hover:text-white">
                            {{ __('Log in') }}
                        </a>
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="rounded-md border border-white/15 bg-white/5 px-4 py-2 transition hover:bg-white/10"
                            >
                                {{ __('Register') }}
                            </a>
                        @endif
                    @endauth
                </nav>
            </header>

            {{-- Hero: brand + one line + CTA --}}
            <main class="mx-auto flex min-h-[calc(100vh-5.5rem)] max-w-6xl flex-col justify-center px-6 pb-16 pt-8 sm:px-8 lg:pb-24">
                <div class="max-w-3xl motion-safe:animate-[fade-up_0.8s_ease-out]">
                    <p class="mb-4 text-sm uppercase tracking-[0.35em] text-[#e88c30]">
                        {{ __('Event operations') }}
                    </p>

                    <h1 class="font-serif text-6xl leading-[0.95] tracking-tight text-[#f4efe6] sm:text-7xl lg:text-8xl">
                        {{ config('app.name', 'RangRez') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#f4efe6]/75 sm:text-xl">
                        {{ __('Plan budgets, track investments and expenses, sell early-bird tickets, and check guests in — all in one place.') }}
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-md bg-[#e88c30] px-5 py-3 text-sm font-semibold text-[#1a1208] transition hover:bg-[#f0a04a]"
                            >
                                {{ __('Open dashboard') }}
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rounded-md bg-[#e88c30] px-5 py-3 text-sm font-semibold text-[#1a1208] transition hover:bg-[#f0a04a]"
                            >
                                {{ __('Team login') }}
                            </a>
                        @endauth

                        @if ($featuredEvent)
                            <a
                                href="{{ route('public.events.show', $featuredEvent) }}"
                                class="rounded-md border border-white/20 px-5 py-3 text-sm font-medium text-[#f4efe6] transition hover:border-white/40 hover:bg-white/5"
                            >
                                {{ __('View :event', ['event' => $featuredEvent->title]) }}
                            </a>
                        @endif
                    </div>
                </div>

                @if ($upcomingEvents->isNotEmpty())
                    <section class="mt-20 motion-safe:animate-[fade-up_1s_ease-out]">
                        <h2 class="mb-4 text-sm uppercase tracking-[0.25em] text-[#f4efe6]/55">
                            {{ __('Upcoming events') }}
                        </h2>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($upcomingEvents as $event)
                                <a
                                    href="{{ route('public.events.show', $event) }}"
                                    class="group block border-t border-white/15 pt-4 transition hover:border-[#e88c30]"
                                >
                                    <div class="text-xl font-medium text-[#f4efe6] group-hover:text-[#e88c30]">
                                        {{ $event->title }}
                                    </div>
                                    <p class="mt-2 text-sm text-[#f4efe6]/60">
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

        <style>
            @keyframes fade-up {
                from {
                    opacity: 0;
                    transform: translateY(18px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>

        @fluxScripts
    </body>
</html>
