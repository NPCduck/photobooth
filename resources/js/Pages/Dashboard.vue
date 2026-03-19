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

    if (diffMin < 1) return 'práve teraz';
    else if (diffMin === 1) return '1 minúta';
    else if (diffMin < 60) return `${diffMin} minút`;
    else if (diffMin < 120) return '1 hodina';
    else if (diffMin < 1440) return `${Math.floor(diffMin / 60)} hodín`;
    else if (diffMin < 2880) return '1 deň';
    else if (diffMin < 43200) return `${Math.floor(diffMin / 1440)} dní`;

    return diffMin;
}
</script>

<template>
    <AuthenticatedLayout>

        <!-- HEADER -->
        <template #header>
            <h2 class="text-2xl md:text-3xl mb-4 font-normal text-gray-800">
                Nástenka
            </h2>
        </template>

        <template #default>
            <div class="flex flex-col gap-6 w-full">

                <!-- STATS -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <DisplayStats>
                        <template #icon><CalendarCheck /></template>
                        <template #value>{{ totalEvents }}</template>
                        Počet eventov
                    </DisplayStats>

                    <DisplayStats>
                        <template #icon><CalendarClock /></template>
                        <template #value>{{ totalUpcomingEvents }}</template>
                        Aktuálne eventy
                    </DisplayStats>

                    <DisplayStats>
                        <template #icon><ShoppingBasket /></template>
                        <template #value>{{ totalOrders }}</template>
                        Celkové objednávky
                    </DisplayStats>

                    <DisplayStats>
                        <template #icon><DollarSign /></template>
                        <template #value>{{ totalRevenue }} €</template>
                        Celkové obraty
                    </DisplayStats>
                </div>

                <!-- UPCOMING EVENTS -->
                <div class="bg-white p-4 md:p-6 shadow rounded-md">
                    <p class="font-thin text-xl md:text-2xl mb-4">
                        Naplánované eventy
                    </p>

                    <div v-if="upcomingEventsList.length" class="flex flex-col gap-4">
                        <EventItem
                            v-for="event in upcomingEventsList"
                            :key="event.id"
                            class="flex flex-row rounded justify-between items-center gap-3 md:flex-row md:items-center"
                        >
                            <template #name>
                                <span class="text-lg md:text-xl break-words">
                                    {{ event.name }}
                                </span>
                            </template>

                            <template #attributes>
                                <div class="text-sm md:text-base">
                                    <span class="font-semibold">Lokácia:</span>
                                    {{ event.details.loc_venue }}
                                    <br class="md:hidden" />
                                    <span class="font-semibold md:ml-2">Dátum:</span>
                                    {{ event.details.date }}
                                </div>
                            </template>

                            <template #buttons>
                                <Link :href="route('events.show', event)">
                                    <div class="hover:bg-itembg rounded-md p-2">
                                        <Eye />
                                    </div>
                                </Link>
                            </template>
                        </EventItem>
                    </div>

                    <div v-else>
                        <p class="text-lg">Neboli nájdené žiadne výsledky</p>
                    </div>
                </div>

                <!-- LATEST ACTIONS -->
                <div class="bg-white p-4 md:p-6 shadow rounded-md">
                    <p class="font-thin text-xl md:text-2xl mb-4">
                        Nedávna aktivita
                    </p>

                    <div v-if="latestActions.length" class="flex flex-col gap-4">
                        <ActionItem
                            v-for="action in latestActions"
                            :key="action.id"
                        >
                            <template #icon><Info /></template>

                            <template #name>
                                <span class="text-base md:text-lg">
                                    {{ action.action_type }} – {{ action.event.name }}
                                </span>
                            </template>

                            <template #ago>
                                <span class="text-xs md:text-sm text-gray-500">
                                    {{ minAgo(action.created_at) }}
                                </span>
                            </template>
                        </ActionItem>
                    </div>

                    <div v-else>
                        <p>Žiadna aktivita</p>
                    </div>
                </div>

            </div>
        </template>
    </AuthenticatedLayout>
</template>
