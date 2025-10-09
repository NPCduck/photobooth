<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarPlus } from 'lucide-vue-next'
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-row justify-between">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    Vytvoriť event
                </h2>
                <Link
                    :href="route('events.store')"
                    class="text-white bg-sidebarbg rounded-md flex flex-row p-2"
                >
                    <CalendarPlus />
                    <span>Uložiť</span>
                </Link>
            </div>
        </template>
        <template #default>
            <form action="">
                <div class="flex flex-col gap-4 w-full">
                    <!-- Basic info -->
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Základné informácie
                        </p>
                        
                    </div>
                </div>
            </form>
        </template>
    </AuthenticatedLayout>
</template>
