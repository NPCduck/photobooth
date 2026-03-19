<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Images, Newspaper } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    event: Object,
    qrurl: String,
})

const svgFile = ref(null);
const svgFrameWrapper = ref(null);

const landingImgFile = ref(null);
const frameImgFile = ref(null);


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

// QR
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

// SVG
function getSvgFrame() {
    return route('private.frameSvg', {
        user_id: props.event.user_id,
        event_id: props.event.id,
        path: 'overlays',
        file: 'frame.svg',
    });
}

async function uploadSvgFrame() {
    if (svgFile.value.files.length === 0) {
        alert('Vyberte SVG soubor k nahrání.');
        return;
    }

    const formData = new FormData();
    formData.append('frame_svg', svgFile.value.files[0]);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        await fetch(route('events.frameSvg.upload', props.event.id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });
        window.location.reload();
    } catch (e) {
        alert('Chyba pri nahrávaní SVG!');
    }
}

async function deleteSvgFrame() {
    if (!confirm('Opravdu chcete odstranit SVG rám? Tato akce je nevratná.')) {
        return;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch(route('events.frameSvg.delete', { event: props.event.id }), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (!response.ok) throw new Error('Chyba pri vymazávaní SVG rámu.');

        // Aktualizuj reaktívny stav
        props.event.overlays.frame_svg = false;

    } catch (e) {
        alert(e.message);
    }
}

async function uploadFrameLandingImgs() {
    if (frameImgFile.value.files.length === 0 || landingImgFile.value.files.length === 0) {
        alert('Vyberte obrázok k nahratiu!');
        return;
    }

    const requests = [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (landingImgFile.value.files.length > 0) {
        const formData = new FormData();
        formData.append('landing_img', landingImgFile.value.files[0]);

        requests.push(fetch(route('events.landingImg.upload', props.event.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        }));
    }

    if (frameImgFile.value.files.length > 0) {
        const formData = new FormData();
        formData.append('frame_img', frameImgFile.value.files[0]);

        requests.push(fetch(route('events.frameImg.upload', props.event.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        }));
    }

    try {
        await Promise.all(requests); // 🔥 parallel upload
        window.location.reload();
    } catch (e) {
        alert('Chyba pri nahrávaní');
    }
}

function deleteFrameImg() {
    if (!confirm('Opravdu chcete odstranit obrázek překryvu? Tato akce je nevratná.')) {
        return;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(route('events.frameImg.delete', { event: props.event.id }), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    }).then(response => {
        if (!response.ok) throw new Error('Chyba při vymazávání obrázku překryvu.');
        props.event.overlays.frame_img = false;
    }).catch(e => {
        alert(e.message);
    });
}

function deleteLandingImg() {
    if (!confirm('Opravdu chcete odstranit obrázek stránky? Tato akce je nevratná.')) {
        return;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(route('events.landingImg.delete', { event: props.event.id }), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    }).then(response => {
        if (!response.ok) throw new Error('Chyba při vymazávání obrázku stránky.');
        props.event.overlays.landing_img = false;
    }).catch(e => {
        alert(e.message);
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
                                    {{ pckg.photo_limit_person == 0 ? 'neobmedzené' : pckg.photo_limit_person }}
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
                            <div v-if="props.event.overlays.landing_img" class="flex flex-col items-center">
                                <img
                                    :src="getImg('overlays', 'landing_img')"
                                    class="w-full max-h-[400px] object-contain mx-auto"
                                />
                                <button @click="deleteLandingImg" class="bg-red-600 text-white p-3 rounded-md hover:bg-red-700 font-semibold mt-4">
                                    Zmazať
                                </button>
                            </div>
                            <div v-else class="flex flex-col items-center">
                                <p >Nemáte nahratý súbor</p>
                                <form @submit.prevent="uploadFrameLandingImgs()" class="w-full mt-4">
                                    <input type="file" name="landing_img" ref="landingImgFile" accept=".jpg,.jpeg,.png" class="border rounded px-3 py-2 w-full">
                                    <button type="submit" class="bg-sidebarbg text-white p-3 rounded-md hover:bg-sidebarbg-dark font-semibold w-full mt-2">
                                        Nahrať
                                    </button>
                                </form>
                            </div>
                            
                            
                        </div>

                        <div class="bg-white p-4 flex flex-col rounded-md gap-4">
                            <p class="font-semibold">Obrázok prekrytia</p>
                            <div v-if="props.event.overlays.frame_img" class="flex flex-col items-center">
                                <img
                                    :src="getImg('overlays', 'frame_img')"
                                    class="max-h-48 sm:max-h-64 object-contain mx-auto"
                                />
                                <button @click="deleteFrameImg" class="bg-red-600 text-white p-3 rounded-md hover:bg-red-700 font-semibold mt-4">
                                    Zmazať
                                </button>
                            </div>
                            <div v-else class="flex flex-col items-center">
                                <p>Nemáte nahratý súbor</p>
                                 <form @submit.prevent="uploadFrameLandingImgs()" class="w-full mt-4">
                                    <input type="file" name="frame_img" ref="frameImgFile" accept=".jpg,.jpeg,.png" class="border rounded px-3 py-2 w-full">
                                    <button type="submit" class="bg-sidebarbg text-white p-3 rounded-md hover:bg-sidebarbg-dark font-semibold w-full mt-2">
                                        Nahrať
                                    </button>
                                </form>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- NASTAVENIA RÁMU -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-xl sm:text-2xl md:text-[25px]">Nastavenia rámu</p>
                    <p v-if="!event.overlays?.frame_svg"
                        class="text-sm text-yellow-600 bg-yellow-100 border border-yellow-300 rounded-md p-3"
                    >
                        Svg súbor nie je nahratý!
                    </p>
                    <div v-if="event.overlays?.frame_svg"
                        class="rounded-md p-3 sm:p-4 flex flex-col gap-4"
                    >
                        <img
                            v-if="event.overlays?.frame_svg"
                            :src="getSvgFrame()"
                            class="w-full max-h-[400px] object-contain mx-auto"
                        />
                    </div>
                    <div class="p-4 text-center">
                        <form class="grid grid-cols-1 md:grid-cols-3 gap-4"
                            @submit.prevent="uploadSvgFrame"
                        >
                            <input type="file" name="frame_svg" ref="svgFile" accept=".svg" class="border rounded px-3 py-2 w-full">
                            <button type="submit" class="bg-sidebarbg text-white p-3 rounded-md hover:bg-sidebarbg-dark font-semibold">
                                Nahrať SVG rám
                            </button>
                            <button
                                @click.prevent="deleteSvgFrame"
                                class="bg-red-600 text-white p-3 rounded-md hover:bg-red-700 font-semibold"
                            >
                                Odstrániť SVG rám
                            </button>
                        </form>
                    </div>
                </div>
                <!-- QR KÓD -->
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
                    <div class="flex flex-row gap-4">
                        <a :href="route('events.export', { event: props.event.id, type: 'emails' })" class="bg-sidebarbg text-white p-2 rounded-md hover:bg-sidebarbg-dark">
                            Exportovať emaily (CSV)
                        </a>
                        <a :href="route('events.export', { event: props.event.id, type: 'photos' })" class="bg-sidebarbg text-white p-2 rounded-md hover:bg-sidebarbg-dark">
                            Exportovať fotky (ZIP)
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>

