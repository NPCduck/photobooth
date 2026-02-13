<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Images, Newspaper } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    event: Object,
    qrurl: String,
})

const overlayPosition = ref(props.event.overlays?.frame_position || 'center');
const overlayStretch = ref(props.event.overlays?.frame_stretch !== false);

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

function saveOverlaySettings() {
    router.patch(route('events.update', props.event.id), {
        name: props.event.name,
        details: {
            type: props.event.details.type,
            hosts: props.event.details.hosts,
            status: props.event.details.status,
            date: props.event.details.date,
            time_start: props.event.details.time_start,
            time_end: props.event.details.time_end,
            loc_venue: props.event.details.loc_venue,
            loc_address: props.event.details.loc_address,
        },
        client: {
            name: props.event.client.name,
            email: props.event.client.email,
            phone: props.event.client.phone,
        },
        packages: props.event.packages,
        overlays: {
            frame_position: overlayPosition.value,
            frame_stretch: overlayStretch.value,
        }
    }, {
        preserveScroll: true,
    });
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

                    <Link
                        :href="route('events.orders.index', props.event)"
                        class="text-white bg-sidebarbg rounded-md flex items-center p-3 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Newspaper />
                        <span>Objednávky</span>
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

                    <Link
                        :href="route('events.orders.index', props.event)"
                        class="text-white bg-sidebarbg rounded-md flex items-center p-3 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Newspaper />
                        <span>Objednávky</span>
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

                <!-- OVERLAY NASTAVENIA -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4" v-if="props.event.overlays?.frame_img">
                    <p class="font-thin text-xl sm:text-2xl md:text-[25px]">Nastavenia prekrytia</p>

                    <div class="bg-overlaybg rounded-md p-3 sm:p-4 flex flex-col gap-4">
                        <!-- Pozícia -->
                        <div class="bg-white p-4 rounded-md flex flex-col gap-3">
                            <label class="font-semibold">Pozícia overlay:</label>
                            <select v-model="overlayPosition" class="border rounded px-3 py-2">
                                <option value="stretch">Roztiahnutý (celá fotka)</option>
                                <option value="top-left">Ľavý horný roh</option>
                                <option value="top-right">Pravý horný roh</option>
                                <option value="bottom-left">Ľavý dolný roh</option>
                                <option value="bottom-right">Pravý dolný roh</option>
                                <option value="center">Stred</option>
                            </select>
                        </div>

                        <!-- Rozťahovanie -->
                        <div class="bg-white p-4 rounded-md flex flex-col gap-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="overlayStretch" class="w-4 h-4">
                                <span class="font-semibold">Rozťahovať overlay na veľkosť fotky</span>
                            </label>
                            <p class="text-sm text-gray-600">
                                Ak je zapnuté, overlay sa roztiahne na rozmery fotky. Ak je vypnuté, zachová si pôvodnú veľkosť a pozícií podľa zvoleného rohu.
                            </p>
                        </div>

                        <button @click="saveOverlaySettings" class="bg-sidebarbg text-white p-3 rounded-md hover:bg-sidebarbg-dark font-semibold">
                            Uložiť nastavenia
                        </button>
                    </div>
                </div>
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        QR kód
                    </p>
                    <div class="flex flex-col gap-4 bg-overlaybg rounded-md p-4">
                        <div class="bg-white flex flex-col items-center p-4 rounded-md gap-4">
                            <div v-if="props.event.qr_active" class="flex flex-col justify-center">
                                <img :src="getQr('qr')" alt="qr_code" class="max-h-[250px] object-contain mb-2">
                                <a :href="qrurl" class="text-sidebarbg hover:underline">{{ qrurl }}</a>
                                <button
                                    @click="deactivateQr()"
                                    class="p-4 bg-red-600 text-white rounded-md hover:bg-red-700 mt-4">
                                    Deaktivovať QR kód
                                </button>
                            </div>
                            <div v-else class="p-4 ">
                                <p class="font-semibold ">
                                    QR kód nie je aktívny
                                </p>
                                <button
                                    @click="activateQr()"
                                    class="p-4 bg-sidebarbg text-white rounded-md hover:bg-sidebarbg-dark mt-4">
                                    Aktivovať QR kód
                                </button>
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

