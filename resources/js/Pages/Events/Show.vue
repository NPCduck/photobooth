<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EventItem from '@/Components/EventItem.vue'
import { Head, Link } from '@inertiajs/vue3';
import { CalendarPlus, Eye, Trash2, Pencil } from 'lucide-vue-next'

import { computed } from 'vue';

const props = defineProps({
    event: Object,
})

function getImg(path, filename) {
    const eventId = props.event.id;
    const userId = props.event.user_id;
    return route('private.image', {
        user_id : userId,
        event_id : eventId,
        path : path,
        file : filename,
    });
}

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-row justify-between mb-4">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    {{ props.event.name }}
                </h2>
                <Link
                    :href="route('events.edit', props.event)"
                    class="text-white bg-sidebarbg rounded-md flex flex-row p-2 gap-2 hover:bg-sidebarbg-dark"
                >
                    <Pencil />
                    <span>Upraviť event</span>
                </Link>
            </div>
        </template>
        <template #default>
            <div class="flex flex-col gap-4 w-full">
                <!-- Container - Detaily -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Detaily
                    </p>
                    <div class="bg-overlaybg grid grid-cols-3 gap-4 p-4 rounded-md">
                        <div class="bg-white flex flex-col gap-4 p-4 rounded-md">
                            <p class="font-semibold text-xl">Základné informácie</p>
                            <div class="flex flex-col gap-2">
                                <div class="flex flex-row justify-between">
                                    <p>Typ</p>
                                    <p class="font-semibold">{{ event.details.type }}</p>
                                </div>
                                <hr>
                                <div class="flex flex-row justify-between">
                                    <p>Dátum</p>
                                    <p class="font-semibold">{{ event.details.date }}</p>
                                </div>
                                <hr>
                                <div class="flex flex-row justify-between">
                                    <p>Čas</p>
                                    <p class="font-semibold">{{ event.details.time_start }} - {{ event.details.time_end }}</p>
                                </div>
                                <hr>
                                <div class="flex flex-row justify-between">
                                    <p>Stav</p>
                                    <p class="font-semibold">{{ event.details.status }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white flex flex-col gap-4 p-4 rounded-md">
                            <p class="font-semibold text-xl">Lokácia</p>
                            <div class="flex flex-col gap-2">
                                <div class="flex flex-col justify-between">
                                    <p>Miesto konania</p>
                                    <p class="font-semibold">{{ event.details.loc_venue }}</p>
                                </div>
                                <hr>
                                <div class="flex flex-col justify-between">
                                    <p>Adresa</p>
                                    <p class="font-semibold">{{ event.details.loc_address }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white flex flex-col gap-4 p-4 rounded-md">
                            <p class="font-semibold text-xl">Klient</p>
                            <div class="flex flex-col gap-2">
                                <div class="flex flex-row justify-between">
                                    <p>Meno</p>
                                    <p class="font-semibold">{{ event.client.name }}</p>
                                </div>
                                <hr>
                                <div class="flex flex-row justify-between">
                                    <p>Email</p>
                                    <p class="font-semibold">{{ event.client.email }}</p>
                                </div>
                                <hr>
                                <div class="flex flex-row justify-between">
                                    <p>Tel. č.</p>
                                    <p class="font-semibold">{{ event.client.phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Container - Balíčky -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Balíčky
                    </p>
                    <div class="flex flex-col gap-4 bg-overlaybg rounded-md p-4">
                        <div v-for="pckg in props.event.packages" :key="pckg.id" class="flex flex-col gap-4 bg-white rounded-md p-4">
                            <div>
                                <p class="font-semibold text-xl">{{ pckg.name }}</p>
                                <hr>
                            </div>
                            
                            <div class="flex flex-row gap-4">
                                <div class="flex flex-row gap-3">
                                    <p class="font-semibold">Cena:</p>
                                    <p>{{ pckg.price }} €</p>
                                </div>
                                <div class="flex flex-row gap-3">
                                    <p class="font-semibold">Celkový limit fotiek:</p>
                                    <p>{{ pckg.photo_limit_total }}</p>
                                </div>
                                <div class="flex flex-row gap-3">
                                    <p class="font-semibold">Limit fotiek na osobu:</p>
                                    <p>{{ !pckg.photo_limit_person ? 'neobmedzené' : pckg.photo_limit_person }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Container - Obrázky eventu -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Obrázky eventu
                    </p>
                    <div class="grid grid-cols-2 grid-row gap-4 bg-overlaybg rounded-md p-4">
                        <div class="bg-white p-4 flex flex-col rounded-md gap-4">
                            <p class="font-semibold">Obrázok stránky</p>
                            <div v-if="props.event.overlays.landing_img" class="flex justify-center">
                                <img :src="getImg('overlays', 'landing_img')" alt="landing_img">
                            </div>
                            <div v-else>
                                <p>Nemáte nahratý súbor</p>
                            </div>
                        </div>
                        <div class="bg-white p-4 flex flex-col rounded-md gap-4">
                            <p class="font-semibold">Obrázok prekrytia fotky</p>
                            <div v-if="props.event.overlays.frame_img" class="flex justify-center">
                                <img :src="getImg('overlays', 'frame_img')" alt="landing_img">
                            </div>
                            <div v-else>
                                <p>Nemáte nahratý súbor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Container - Exportovať dáta -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Exportovať dáta
                    </p>
                    <div class="flex flex-col gap-4">
                        <form @submit.prevent="extractPhotos" class="flex flex-col gap-4">
                            <div class="flex flex-row gap-4 items-center">
                                <input type="radio" name="exportType" id="exportEmail">
                                <label for="exportEmial">Email list (CSV)</label>
                            </div>

                            <div class="flex flex-row gap-4 items-center">
                                <input type="radio" name="exportType" id="exportZip">
                                <label for="exportEmial">Fotky (ZIP)</label>
                            </div>

                            <button class="bg-sidebarbg text-white self-start p-2 rounded-md hover:bg-sidebarbg-dark">
                                Exportovať
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
