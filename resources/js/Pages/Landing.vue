<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingBasket, Newspaper, Box, BookText, Book, ShoppingBag } from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Úvod" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="/background.svg"
        />
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header
                    class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3"
                >
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <img src="/PB_logo_color.svg" alt="Logo" class="h-24 w-auto" />
                    </div>
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-black font-medium ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <div class="flex flex-col items-center gap-4 md:flex-row md:gap-6">
                                <Link
                                    :href="route('login')"
                                    class="rounded-md px-3 py-2 text-black font-medium ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                >
                                    Prihlásiť sa
                                </Link>

                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="rounded-md px-3 py-2 text-black font-medium ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                >
                                    Vytvoriť účet
                                </Link>
                            </div>
                        </template>
                    </nav>
                </header>

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <a
                            href="https://laravel.com/docs"
                            id="docs-card"
                            class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                        >
                            <div
                                id="screenshot-container"
                                class="relative flex w-full flex-1 items-stretch"
                            >
                                <img
                                    src="https://laravel.com/assets/img/welcome/docs-light.svg"
                                    alt="Laravel documentation screenshot"
                                    class="aspect-video h-full w-full flex-1 rounded-[10px] object-cover object-top drop-shadow-[0px_4px_34px_rgba(0,0,0,0.06)] dark:hidden"
                                    @error="handleImageError"
                                />
                                <img
                                    src="https://laravel.com/assets/img/welcome/docs-dark.svg"
                                    alt="Laravel documentation screenshot"
                                    class="hidden aspect-video h-full w-full flex-1 rounded-[10px] object-cover object-top drop-shadow-[0px_4px_34px_rgba(0,0,0,0.25)] dark:block"
                                />
                                <div
                                    class="absolute -bottom-16 -left-16 h-40 w-[calc(100%+8rem)] bg-gradient-to-b from-transparent via-white to-white dark:via-zinc-900 dark:to-zinc-900"
                                ></div>
                            </div>

                            <div
                                class="relative flex items-center gap-6 lg:items-end"
                            >
                                <div
                                    id="docs-card-content"
                                    class="flex items-start gap-6 lg:flex-col"
                                >
                                    <div
                                        class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF3BFA]/10 sm:size-16"
                                    >
                                        <BookText
                                            class="size-5 sm:size-6 text-[#FF3BFA]"
                                        />
                                    </div>

                                    <div class="pt-3 sm:pt-5 lg:pt-0">
                                        <h2
                                            class="text-xl font-semibold text-black dark:text-white"
                                        >
                                            Dokumentácia
                                        </h2>

                                        <p class="mt-4 text-sm/relaxed">
                                            Laravel has wonderful documentation
                                            covering every aspect of the
                                            framework. Whether you are a
                                            newcomer or have prior experience
                                            with Laravel, we recommend reading
                                            our documentation from beginning to
                                            end.
                                        </p>
                                    </div>
                                </div>

                                <svg
                                    class="size-6 shrink-0 stroke-[#FF3BFA]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                    />
                                </svg>
                            </div>
                        </a>

                        <a
                            href="https://laracasts.com"
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF3BFA]/10 sm:size-16"
                            >
                                <ShoppingBasket
                                    class="size-5 sm:size-6 text-[#FF3BFA]"
                                />
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2
                                    class="text-xl font-semibold text-black dark:text-white"
                                >
                                    Cenové balíky
                                </h2>

                                <p class="mt-4 text-sm/relaxed">
                                    Laracasts offers thousands of video
                                    tutorials on Laravel, PHP, and JavaScript
                                    development. Check them out, see for
                                    yourself, and massively level up your
                                    development skills in the process.
                                </p>
                            </div>

                            <svg
                                class="size-6 shrink-0 self-center stroke-[#FF3BFA]"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                />
                            </svg>
                        </a>

                        <a
                            href="https://laravel-news.com"
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF3BFA]/10 sm:size-16"
                            >
                                <Newspaper
                                    class="size-5 sm:size-6 text-[#FF3BFA]"
                                />
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2
                                    class="text-xl font-semibold text-black dark:text-white"
                                >
                                    Novinky
                                </h2>

                                <p class="mt-4 text-sm/relaxed">
                                    Laravel News is a community driven portal
                                    and newsletter aggregating all of the latest
                                    and most important news in the Laravel
                                    ecosystem, including new package releases
                                    and tutorials.
                                </p>
                            </div>

                            <svg
                                class="size-6 shrink-0 self-center stroke-[#FF3BFA]"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                />
                            </svg>
                        </a>

                        <div
                            class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF3BFA]/10 sm:size-16"
                            >
                                <Box class="size-5 sm:size-6 text-[#FF3BFA]" />
                            </div>

                            <div class="pt-3 sm:pt-5">
                                <h2
                                    class="text-xl font-semibold text-black dark:text-white"
                                >
                                    Predstavenie produktu
                                </h2>

                                <p class="mt-4 text-sm/relaxed">
                                    Laravel's robust library of first-party
                                    tools and libraries, such as
                                    <a
                                        href="https://forge.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white dark:focus-visible:ring-[#FF2D20]"
                                        >Forge</a
                                    >,
                                    <a
                                        href="https://vapor.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Vapor</a
                                    >,
                                    <a
                                        href="https://nova.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Nova</a
                                    >,
                                    <a
                                        href="https://envoyer.io"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Envoyer</a
                                    >, and
                                    <a
                                        href="https://herd.laravel.com"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Herd</a
                                    >
                                    help you take your projects to the next
                                    level. Pair them with powerful open source
                                    libraries like
                                    <a
                                        href="https://laravel.com/docs/billing"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Cashier</a
                                    >,
                                    <a
                                        href="https://laravel.com/docs/dusk"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Dusk</a
                                    >,
                                    <a
                                        href="https://laravel.com/docs/broadcasting"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Echo</a
                                    >,
                                    <a
                                        href="https://laravel.com/docs/horizon"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Horizon</a
                                    >,
                                    <a
                                        href="https://laravel.com/docs/sanctum"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Sanctum</a
                                    >,
                                    <a
                                        href="https://laravel.com/docs/telescope"
                                        class="rounded-sm underline hover:text-black focus:outline-none focus-visible:ring-1 focus-visible:ring-[#FF2D20] dark:hover:text-white"
                                        >Telescope</a
                                    >, and more.
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <footer
                    class="py-16 text-center text-sm text-black dark:text-white/70"
                >
                    Vytvoril Patrik Kvasničák &copy; {{ new Date().getFullYear() }}
                </footer>
            </div>
        </div>
    </div>
</template>
