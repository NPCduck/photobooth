<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Images } from 'lucide-vue-next';

const props = defineProps({
    event: Object,
    qrurl: String,
})

function getImg(path, filename) {
    const eventId = props.event.id;
    const userId = props.event.user_id;

    const params = {
        user_id: userId,
        event_id: eventId,
        file: filename,
    };
    if (path) {
        params.path = path;
    }
    return route('private.image', params);
}

function getQr(filename) {
    const eventId = props.event.id;
    const userId = props.event.user_id;

    const params = {
        user_id: userId,
        event_id: eventId,
        file: filename,
    };
    return route('private.qrcode', params);
}

function activateQr() {
    router.post(route('events.qr.activate', props.event.id));
}
function deactivateQr() {
    router.post(route('events.qr.deactivate', props.event.id));
}

</script>

<template>
    <AuthenticatedLayout>
        <!-- HEADER -->
        <template #header>
            <div class="flex flex-col md:flex-row justify-between gap-4 md:mb-4">
                <h2 class="text-2xl md:text-3xl font-normal text-gray-800">
                    {{ props.event.name }}
                </h2>

                <!-- Desktop buttons -->
                <div class="hidden md:flex flex-row gap-4">
                    <Link
                        :href="route('events.photos', props.event)"
                        class="text-white bg-sidebarbg rounded-md flex items-center p-2 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Images />
                        <span>Zobraziť fotky</span>
                    </Link>

                    <Link
                        :href="route('events.edit', props.event)"
                        class="text-white bg-sidebarbg rounded-md flex items-center p-2 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Pencil />
                        <span>Upraviť event</span>
                    </Link>
                </div>
            </div>
        </template>

        <!-- CONTENT -->
        <template #default>
            <div class="flex flex-col gap-4 w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-0">

                <!-- Mobile buttons -->
                <div class="flex md:hidden flex-col gap-3">
                    <Link
                        :href="route('events.photos', props.event)"
                        class="text-white bg-sidebarbg rounded-md flex items-center p-3 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Images />
                        <span>Zobraziť fotky</span>
                    </Link>

                    <Link
                        :href="route('events.edit', props.event)"
                        class="text-white bg-sidebarbg rounded-md flex items-center p-3 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Pencil />
                        <span>Upraviť event</span>
                    </Link>
                </div>

                <!-- DETAILY -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-xl sm:text-2xl md:text-[25px]">Detaily</p>

                    <div class="bg-overlaybg grid grid-cols-1 md:grid-cols-3 gap-4 p-3 sm:p-4 rounded-md">
                        <!-- Základné info -->
                        <div class="bg-white flex flex-col gap-4 p-4 rounded-md">
                            <p class="font-semibold text-lg sm:text-xl">Základné informácie</p>

                            <div class="flex flex-col gap-2">
                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Typ</p>
                                    <p class="font-semibold">{{ event.details.type }}</p>
                                </div>
                                <hr>

                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Dátum</p>
                                    <p class="font-semibold">{{ event.details.date }}</p>
                                </div>
                                <hr>

                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Čas</p>
                                    <p class="font-semibold">
                                        {{ event.details.time_start }} - {{ event.details.time_end }}
                                    </p>
                                </div>
                                <hr>

                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Stav</p>
                                    <p class="font-semibold">{{ event.details.status }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Lokácia -->
                        <div class="bg-white flex flex-col gap-4 p-4 rounded-md">
                            <p class="font-semibold text-lg sm:text-xl">Lokácia</p>

                            <div class="flex flex-col gap-2">
                                <div>
                                    <p>Miesto konania</p>
                                    <p class="font-semibold">{{ event.details.loc_venue }}</p>
                                </div>
                                <hr>

                                <div>
                                    <p>Adresa</p>
                                    <p class="font-semibold">{{ event.details.loc_address }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Klient -->
                        <div class="bg-white flex flex-col gap-4 p-4 rounded-md">
                            <p class="font-semibold text-lg sm:text-xl">Klient</p>

                            <div class="flex flex-col gap-2">
                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Meno</p>
                                    <p class="font-semibold">{{ event.client.name }}</p>
                                </div>
                                <hr>

                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Email</p>
                                    <p class="font-semibold break-all">{{ event.client.email }}</p>
                                </div>
                                <hr>

                                <div class="flex flex-col sm:flex-row justify-between">
                                    <p>Tel. č.</p>
                                    <p class="font-semibold">{{ event.client.phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BALÍČKY -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-xl sm:text-2xl md:text-[25px]">Balíčky</p>

                    <div class="flex flex-col gap-4 bg-overlaybg rounded-md p-3 sm:p-4">
                        <div
                            v-for="pckg in props.event.packages"
                            :key="pckg.id"
                            class="flex flex-col gap-4 bg-white rounded-md p-4"
                        >
                            <p class="font-semibold text-lg sm:text-xl">{{ pckg.name }}</p>
                            <hr>

                            <div class="flex flex-col sm:flex-row flex-wrap gap-4">
                                <div><span class="font-semibold">Cena:</span> {{ pckg.price }} €</div>
                                <div><span class="font-semibold">Limit spolu:</span> {{ pckg.photo_limit_total }}</div>
                                <div>
                                    <span class="font-semibold">Na osobu:</span>
                                    {{ pckg.photo_limit_person ?? 'neobmedzené' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OBRÁZKY -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-xl sm:text-2xl md:text-[25px]">Obrázky eventu</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-overlaybg rounded-md p-3 sm:p-4">
                        <div class="bg-white p-4 flex flex-col rounded-md gap-4">
                            <p class="font-semibold">Obrázok stránky</p>
                            <img
                                v-if="props.event.overlays.landing_img"
                                :src="getImg('overlays', 'landing_img')"
                                class="w-full max-h-[400px] object-contain mx-auto"
                            />
                            <p v-else>Nemáte nahratý súbor</p>
                        </div>

                        <div class="bg-white p-4 flex flex-col rounded-md gap-4">
                            <p class="font-semibold">Obrázok prekrytia</p>
                            <img
                                v-if="props.event.overlays.frame_img"
                                :src="getImg('overlays', 'frame_img')"
                                class="max-h-48 sm:max-h-64 object-contain mx-auto"
                            />
                            <p v-else>Nemáte nahratý súbor</p>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </AuthenticatedLayout>
</template>

