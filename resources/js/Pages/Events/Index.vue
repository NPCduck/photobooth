<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventItem from '@/Components/EventItem.vue'
import { Head, Link } from '@inertiajs/vue3';
import { CalendarPlus, Eye, Trash2, Pencil } from 'lucide-vue-next'

const props = defineProps({
    events: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-row justify-between mb-4">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    List eventov
                </h2>
                <Link
                    :href="route('events.create')"
                    class="text-white bg-sidebarbg rounded-md flex flex-row p-2 gap-2 hover:bg-sidebarbg-dark"
                >
                    <CalendarPlus />
                    <span>Vytvoriť event</span>
                </Link>
            </div>
        </template>
        <template #default>
            <div>
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Zoznam eventov
                    </p>
                    <ul>
                        <div v-if="events.length" class="flex flex-col gap-4">
                            <EventItem
                                v-for="event in events"
                                :key="event.name"
                                class="rounded-md align-center flex"
                            >
                                <template #name>
                                    <span class="text-xl truncate w-32 inline-block">{{ event.name }}</span>
                                </template>

                                <template #attributes>
                                    <div class="flex flex-row gap-2">
                                        <div>
                                            <span><b>Lokácia:</b></span>
                                            {{ event.details.loc_venue }}
                                        </div>
                                        <div>
                                            <span><b>Dátum:</b></span>
                                            {{ event.details.date }}
                                        </div>
                                        <div>
                                            <span><b>Obraty:</b></span>
                                            {{ event.orders.filter(order => order.status === 'completed').reduce((sum, order) => sum + order.amount, 0) }} €
                                        </div>
                                        
                                    </div>
                                </template>
                                
                                <template #buttons>
                                    <Link
                                        :href="route('events.show', event)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Eye />
                                        </div>
                                    </Link>
                                    <Link
                                        :href="route('events.show', event)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Pencil />
                                        </div>
                                    </Link>
                                    <Link
                                        :href="route('events.show', event)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Trash2 />
                                        </div>
                                    </Link>
                                </template>
                            </EventItem>
                        </div>
                        
                        <div v-else>
                            <h2 class="text-[20px]">
                                Neboli nájdené žiadne výsledky
                            </h2>
                        </div>
                    </ul>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
