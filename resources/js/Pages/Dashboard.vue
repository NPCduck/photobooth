<script setup>
import { CalendarCheck, CalendarClock, DollarSign, ShoppingBasket, Eye, Info } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DisplayStats from '@/Components/DisplayStats.vue';
import EventItem from '@/Components/EventItem.vue';
import ActionItem from '@/Components/ActionItem.vue';

const props = defineProps({
    totalEvents: Number,
    totalUpcomingEvents: Number,
    totalOrders: Number,
    totalRevenue: Number,
    upcomingEventsList: Array,
    latestActions: Array
});

function minAgo (timestamp) {
    const created = new Date(timestamp);
    const now = new Date();
    const diffMs = now - created;
    const diffMin = Math.floor(diffMs / 1000 / 60);
    return diffMin;
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-3xl mb-4 font-normal leading-tight text-gray-800">
                Nástenka
            </h2>
        </template>
        <template #default>
            <div class="flex flex-col gap-4 w-full">
                <!-- Basic stats -->
                <div class="flex justify-between flex-row gap-4">
                    <DisplayStats>
                        <template #icon>
                            <CalendarCheck />
                        </template>
                        <template #value>
                            {{ totalEvents }}
                        </template>
                        Počet eventov
                    </DisplayStats>
                    <DisplayStats>
                        <template #icon>
                            <CalendarClock />
                        </template>
                        <template #value>
                            {{ totalUpcomingEvents }}
                        </template>
                        Aktuálne eventy
                    </DisplayStats>
                    <DisplayStats>
                        <template #icon>
                            <ShoppingBasket />
                        </template>
                        <template #value>
                            {{ totalOrders }}
                        </template>
                        Celkové objednávky
                    </DisplayStats>
                    <DisplayStats>
                        <template #icon>
                            <DollarSign />
                        </template>
                        <template #value>
                            {{ totalRevenue }} €
                        </template>
                        Celkové obraty
                    </DisplayStats>
                </div>

                <!-- Upcoming events -->
                <div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Naplánované eventy
                        </p>
                        <ul>
                            <div v-if="upcomingEventsList.length" class="flex flex-col gap-4">
                                <EventItem
                                    v-for="event in upcomingEventsList"
                                    :key="event.name"
                                    class="rounded-md align-center flex"
                                >
                                    <template #name>
                                        <span class="text-xl">{{ event.name }}</span>
                                    </template>

                                    <template #attributes>
                                        <div>
                                            <span><b>Lokácia:</b></span>
                                            {{ event.details.loc_venue }}
                                            <span><b>Dátum:</b></span>
                                            {{ event.details.date }}
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

                <!-- Latest actions -->
                <div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="text-[25px] font-[100]">
                            Nedávna aktivita
                        </p>
                        <ul>
                            <div
                                v-if="latestActions.length"
                                class="flex flex-1 h-38 flex-col gap-4"
                            >
                                <ActionItem v-for="action in latestActions" :key="action.id">
                                    <template #icon>
                                        <Info />
                                    </template>
                                    <template #name>
                                        <span class="text-lg">{{ action.action_type }} - {{ action.event.name }}</span>
                                    </template>
                                    <template #ago>
                                        <span class="text-gray-500 text-sm">{{ minAgo(action.created_at) }} min</span>
                                    </template>
                                </ActionItem>
                            </div>
                            <div v-else>
                                <h2>
                                    Neboli nájdené žiadne výsledky
                                </h2>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
