<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Input from '@/Components/Input.vue';
import Package from '@/Components/Package.vue';

import VueDatepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import Swal from 'sweetalert2';

import { toRaw } from 'vue';
import { nextTick } from 'vue';

import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref, reactive } from 'vue';

const today = new Date();
today.setHours(0, 0, 0, 0);
const minDate = ref(today);

const form = useForm({
    name: null,
    details: {
        date: null,
        type: null,
        time_start: null,
        time_end: null,
        status: null,
        hosts: null,
        loc_venue: null,
        loc_address: null,
    },
    packages: [],
    overlays: {
        landing_img: null,
        frame_img: null,
    },
    client: {
        name: null,
        email: null,
        phone: null,
    }
});

const statuses = [
    {title : 'aktuálny'},
    {title : 'ukončený'}
]

const submit = () => {
    //úprava date formátu
    if (form.details.date) {
        const dateObj = new Date(form.details.date);
        const formattedDate = dateObj.toISOString().split('T')[0];
        form.details.date = formattedDate;
    }

    //úprava time formátu
    form.details.time_start = formatTime(form.details.time_start);
    form.details.time_end   = formatTime(form.details.time_end);

    //odoslanie na back-end
    form.post(route('events.store'), {
        onSuccess: () => {
            form.reset();
            packageId = 1;
            console.log('Successfully sent!');
        },
        onError: (errors) => {
        }
    });
}

function formatTime(time) {
    if (time) {
        const h = String(time.hours).padStart(2, '0');
        const m = String(time.minutes).padStart(2, '0');
        return `${h}:${m}`;
    } else { return }
}

const dateFormat = () => {
    if (!form.details.date) return ''
    const d = new Date(form.details.date);
    return d.toLocaleDateString('sk-SK');
}

function showError(message = 'Niečo sa pokazilo!', title = 'Chyba...') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message
    });
}

const handleFileUpload = function(e, key) {
    const file = e.target.files[0];

    if (!file) {
        showError('Súbor sa nenašiel!');
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showError('Podporované typy: jpg, png, webp', 'Nesprávny typ súboru!');
        e.target.value = '';
        return;
    }

    if (file.size > 4 * 1024 * 1024) {
        showError('Maximálna veľkosť: 4MB', 'Súbor je moc veľký!');
        e.target.value = '';
        return;
    }

    form.overlays[key] = file; // <- TOTO je kľúčové
};

let packageId = 1;

const generatePackage = () => ({
    id : packageId++,
    name : '',
    price : null,
    photo_limit_total : null,
    photo_limit_person : null,
});

const addPackage = () => {
    form.packages.push(generatePackage());
}

const removePackage = (index) => {
    form.packages.splice(index, 1);
}

const testInputs = async () => {
    await nextTick();
    console.log(JSON.stringify(toRaw(form), null, 2));
}

addPackage();
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-row justify-between">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    Vytvoriť event
                </h2>
                <div class="flex flex-row gap-4">
                    <Link
                        @click="submit"
                        class="text-white bg-sidebarbg rounded-md flex flex-row p-2 gap-2 hover:bg-sidebarbg-dark"
                    >
                        <Save />
                        <span>Uložiť</span>
                    </Link>
                    <Link
                        :href="route('events.index')"
                        class="text-white bg-red-600 rounded-md flex flex-row p-2 gap-2 hover:bg-red-700"
                    >
                        <Trash2 />
                        <span>Zahodiť</span>
                    </Link>
                </div>

            </div>
        </template>
        <template #default>
            <button type="button" @click="testInputs" class="absolute top-2 left-[700px] p-4 bg-green-400 rounded-md">
                Test inputov
            </button>
            <form @submit.prevent="submit">
                <div class="flex flex-col gap-4 w-full">
                    <!-- Basic info -->
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Základné informácie
                        </p>
                        <div class="grid grid-cols-4 gap-4">
                            <Input id="name" label="Názov eventu" v-model="form.name" :error="form.errors['name']" />
                            <Input id="type" label="Typ eventu" v-model="form.details.type" :error="form.errors['details.type']" />
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="date" class="font-semibold">Dátum</label>
                                <VueDatepicker 
                                    id="date"
                                    :type="'date'"
                                    :enable-time-picker="false"
                                    v-model="form.details.date"
                                    :format="dateFormat"
                                    locale="sk"
                                    :auto-apply="true"
                                    placeholder="Vyberte dátum"
                                    class="rounded-md border-0 bg-overlaybg w-full"
                                    :min-date="minDate"
                                />
                                <p v-if="form.errors['details.date']" class="text-sm text-red-600 font-bold">
                                    Toto pole je povinné.
                                </p>
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="time_start" class="font-semibold">Čas - začiatok</label>
                                <VueDatepicker
                                    id="time_start"
                                    v-model="form.details.time_start"
                                    type="time"
                                    time-picker
                                    :is24="true"
                                    format="HH:mm"
                                    locale="sk"
                                    cancel-text="Zrušiť"
                                    select-text="Vybrať"
                                    placeholder="Vyberte čas"
                                    :action-texts="{
                                        cancel: 'Zrušiť',
                                        select: 'Vybrať',
                                    }"
                                    class="rounded-md bg-overlaybg border-0"
                                />
                                <p v-if="form.errors['details.time_start']" class="text-sm text-red-600 font-bold">
                                    Toto pole je povinné.
                                </p>
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="time_end" class="font-semibold">Čas - koniec</label>
                                <VueDatepicker
                                    id="time_end"
                                    v-model="form.details.time_end"
                                    type="time"
                                    time-picker
                                    :is24="true"
                                    format="HH:mm"
                                    locale="sk"
                                    placeholder="Vyberte čas"
                                    cancel-text="Zrušiť"
                                    select-text="Vybrať"
                                    class="rounded-md bg-overlaybg border-0"
                                />
                                <p v-if="form.errors['details.time_end']" class="text-sm text-red-600 font-bold">
                                    Toto pole je povinné.
                                </p>
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="status" class="font-semibold">Status</label>
                                <select id="status" v-model="form.details.status" class="rounded-md bg-overlaybg border-0" :error="form.errors['details.status']">
                                    <option v-for="status in statuses" :key="status.title" :value="status.title">{{ status.title }}</option>
                                </select>
                                <p v-if="form.errors['details.status']" class="text-sm text-red-600 font-bold">
                                    Toto pole je povinné.
                                </p>
                            </div>
                            <Input id="hosts" label="Počet očakávaných hostí" type="number" v-model="form.details.hosts"  :error="form.errors['details.hosts']" />
                            
                        </div>
                    </div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Lokácia a detaily
                        </p>
                        <div class="grid grid-cols-2 gap-4 items-start">
                            <Input id="loc_venue" label="Miesto konania" v-model="form.details.loc_venue" :error="form.errors['details.loc_venue']" />
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="status" class="font-semibold">Celá adresa</label>
                                <textarea id="loc_address" v-model="form['details']['loc_address']" class="border-0 rounded-md bg-overlaybg h-32"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Balíčky
                        </p>
                        <div class="flex flex-col gap-4">
                            <Package
                                v-for="(pckg, index) in form.packages"
                                :key="pckg.id"
                                :model="pckg"
                                @remove="removePackage(index)"
                                :errors="{
                                    name: form.errors[`packages.${index}.name`],
                                    price: form.errors[`packages.${index}.price`],
                                    photo_limit_total: form.errors[`packages.${index}.photo_limit_total`],
                                    photo_limit_person: form.errors[`packages.${index}.photo_limit_person`],
                                }"
                            >
                                <template #remove>
                                    <div v-if="pckg !== form.packages[0]" class="absolute top-[50%] translate-y-[-50%] right-[-40px] bg-red-600 rounded-md p-1 hover:bg-red-700 flex items-center">
                                        <button
                                            type="button"
                                            @click="$emit('remove')"
                                        >
                                            <Trash2 class="text-white translate-y-[-1px]" />
                                        </button>
                                    </div>
                                </template>
                            </Package> 
                        </div>
                        <span class="ml-auto mr-auto rounded-full border border-sidebarbg p-2 bg-overlaybg cursor-pointer hover:bg-overlaybg-dark"
                            @click="addPackage"
                        >
                            <Plus class="text-sidebarbg" />
                        </span>
                    </div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Informácie o klientovi
                        </p>
                        <div class="grid grid-cols-3 gap-4">
                            <Input id="clientName" label="Meno klienta" v-model="form.client.name" :error="form.errors['client.name']" />
                            <Input id="clientEmail" label="Email klienta" v-model="form.client.email" :error="form.errors['client.email']" />
                            <Input id="clientPhone" label="Tel. č. klienta" v-model="form.client.phone" :error="form.errors['client.phone']" />
                        </div>
                    </div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Dodatočné nastavenia
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="landingImg" class="font-semibold">Obrázok hlavnej stránky</label>
                                <input type="file" @change="e => handleFileUpload(e, 'landing_img')" />
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="frameImg" class="font-semibold">Obrázok rámu kamery</label>
                                <input type="file" @change="e => handleFileUpload(e, 'frame_img')" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row gap-4 ml-auto">
                        <Link
                            @click="submit"
                            class="text-white bg-sidebarbg rounded-md flex flex-row p-2 gap-2 hover:bg-sidebarbg-dark"
                        >
                            <Save />
                            <span>Uložiť</span>
                        </Link>
                        <Link
                            :href="route('events.index')"
                            class="text-white bg-red-600 rounded-md flex flex-row p-2 gap-2 hover:bg-red-700"
                        >
                            <Trash2 />
                            <span>Zahodiť</span>
                        </Link>
                    </div>
                </div>
            </form>
        </template>
    </AuthenticatedLayout>
</template>
