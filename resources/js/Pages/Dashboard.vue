<script setup>
import { CalendarCheck, CalendarClock, DollarSign, ShoppingBasket, Eye } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DisplayStats from '@/Components/DisplayStats.vue';
import EventItem from '@/Components/EventItem.vue';

const props = defineProps({
    totalEvents: Number,
    totalUpcomingEvents: Number,
    totalOrders: Number,
    totalRevenue: Number,
    upcomingEventsList: Array,
    latestActions: Array
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl mb-4 font-semibold leading-tight text-gray-800">
                Nástenka
            </h2>
        </template>
        <template #default>
            <div class="ml-4 mr-4 flex flex-col gap-4">
                <!-- Basic stats -->
                <div class="flex justify-between flex-row gap-4">
                    <DisplayStats>
                        <template #icon>
                            <CalendarCheck />
                        </template>
                        <template #value>
                            {{ props.totalEvents }}
                        </template>
                        Počet eventov
                    </DisplayStats>
                    <DisplayStats>
                        <template #icon>
                            <CalendarClock />
                        </template>
                        <template #value>
                            {{ props.totalUpcomingEvents }}
                        </template>
                        Aktuálne eventy
                    </DisplayStats>
                    <DisplayStats>
                        <template #icon>
                            <ShoppingBasket />
                        </template>
                        <template #value>
                            {{ props.totalOrders }}
                        </template>
                        Celkové objednávky
                    </DisplayStats>
                    <DisplayStats>
                        <template #icon>
                            <DollarSign />
                        </template>
                        <template #value>
                            {{ props.totalRevenue }} €
                        </template>
                        Celkové obraty
                    </DisplayStats>
                </div>

                <!-- Upcoming events -->
                <div>
                    <p class="text-[25px] font-[100]">
                        Naplánované eventy
                    </p>
                    <div class="flex flex-col bg-white shadow rounded-md h-64">
                        <ul>
                            <div v-if="upcomingEventsList.length">
                                <EventItem v-for="event in upcomingEventsList" :key="event.name">
                                    <template #name>
                                        {{ event.name }}
                                    </template>

                                    <template #attributes>
                                        <div>
                                            <span>Lokácia:</span>
                                            {{ event.details.loc_address }}
                                        </div>
                                    </template>
                                        <div>
                                            <Link
                                                :href="route('events.show', event.id)"
                                            >
                                                <Eye />
                                            </Link>
                                        </div>
                                    <template #buttons>

                                    </template>
                                </EventItem>
                            </div>
                            
                            <div v-else>
                                <h2 class="text-[20px]">
                                    Neboli nájdené žiadne výsledky!
                                </h2>
                            </div>
                        </ul>
                    </div>
                </div>

                <!-- Latest actions -->
                <div>
                    <p class="text-[25px] font-[100]">
                        Nedávna aktivita
                    </p>
                    <div class="flex flex-col bg-white shadow rounded-md h-64">
                        <ul>

                        </ul>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
