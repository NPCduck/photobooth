<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Input from '@/Components/Input.vue';
import Package from '@/Components/Package.vue';

import VueDatepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import Swal from 'sweetalert2';

import { toRaw } from 'vue';
import { nextTick } from 'vue';

import { Link, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const today = new Date();
today.setHours(0, 0, 0, 0);
const minDate = ref(today);

const props = defineProps({
    event: Object,
});

console.log(props.event);

const form = useForm({
    name: props.event.name,
    details: {
        date: props.event.details.date,
        type: props.event.details.type,
        time_start: props.event.details.time_start,
        time_end: props.event.details.time_end || null,
        status: props.event.details.status,
        hosts: props.event.details.hosts,
        loc_venue: props.event.details.loc_venue,
        loc_address: props.event.details.loc_address || null,
    },
    packages: props.event.packages,
    overlays: {
        landing_img: props.event.overlays?.landing_img ?? null,
        frame_img: props.event.overlays?.frame_img ?? null,
        file_landing: null,
        file_frame: null,
    },
    client: {
        name: props.event.client?.name ?? null,
        email: props.event.client?.email ?? null,
        phone: props.event.client?.phone ?? null,
    }
});

if (props.event.details.time_start) {
    const [h, m] = props.event.details.time_start.split(':');
    form.details.time_start = { hours: parseInt(h), minutes: parseInt(m) };
}

if (props.event.details.time_end) {
    const [h, m] = props.event.details.time_end.split(':');
    form.details.time_end = { hours: parseInt(h), minutes: parseInt(m) };
}

const statuses = [
    {title : 'aktuálny'},
    {title : 'ukončený'}
]

const submit = () => {
    // Normalize date/time fields into strings expected by the backend
    const dateObj = new Date(form.details.date);
    const formattedDate = dateObj.toISOString().split('T')[0];
    form.details.date = formattedDate;

    form.details.time_start = formatTime(form.details.time_start);
    form.details.time_end   = formatTime(form.details.time_end);

    // Build FormData explicitly so nested objects + File instances are serialized
    const fd = new FormData();

    // Add the _method override for PUT (Inertia will respect redirects)
    fd.append('_method', 'PUT');

    // Recursive serializer that produces bracketed keys recognized by PHP/Laravel
    const buildFormData = (formData, data, parentKey = null) => {
        if (data === null || data === undefined) {
            // append empty string for nullish values so Laravel receives the key
            formData.append(parentKey, '');
            return;
        }

        // File instance -> append directly
        if (data instanceof File) {
            formData.append(parentKey, data);
            return;
        }

        // If it's a Date, append ISO string
        if (data instanceof Date) {
            formData.append(parentKey, data.toISOString());
            return;
        }

        if (Array.isArray(data)) {
            data.forEach((value, index) => {
                const key = `${parentKey}[${index}]`;
                if (typeof value === 'object' && !(value instanceof File)) {
                    buildFormData(formData, value, key);
                } else {
                    buildFormData(formData, value, key);
                }
            });
            return;
        }

        if (typeof data === 'object') {
            Object.keys(data).forEach((prop) => {
                const value = data[prop];
                const key = parentKey ? `${parentKey}[${prop}]` : prop;
                if (typeof value === 'object' && !(value instanceof File) && value !== null) {
                    buildFormData(formData, value, key);
                } else {
                    buildFormData(formData, value, key);
                }
            });
            return;
        }

        // Primitive -> append
        formData.append(parentKey, String(data));
    };

    // Start serialization from top-level form object
    buildFormData(fd, toRaw(form));

    // Debug: list FormData entries in console (remove in production)
    // eslint-disable-next-line no-console
    for (const pair of fd.entries()) {
        // Print key and type of value to avoid large binary logging
        const val = pair[1];
        console.log('FormData key:', pair[0], 'value type:', val instanceof File ? `File(${val.name})` : typeof val);
    }

    // Submit via Inertia so redirects and validation errors are handled consistently
    Inertia.post(route('events.update', props.event), fd, {
        forceFormData: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
            packageId = 1;
            console.log('Successfully sent!');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
}

function formatTime(time) {
    if (!time) return null;

    if (typeof time === 'string') {
        return time;
    }

    const h = String(time.hours).padStart(2, '0');
    const m = String(time.minutes).padStart(2, '0');
    return `${h}:${m}`;
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

const handleFileUpload = function(e, overlay) {
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

    form.overlays[overlay] = e.target.files[0];
}

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
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-row justify-between">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    Upraviť event
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
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="status" class="font-semibold">Status</label>
                                <select id="status" v-model="form.details.status" class="rounded-md bg-overlaybg border-0">
                                    <option v-for="status in statuses" :key="status.title" :value="status.title">{{ status.title }}</option>
                                </select>
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
                                :is-first="pckg === form.packages[0]"
                                @remove="removePackage(index)"
                            /> 
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
                                <input type="file" @change="e => handleFileUpload(e, 'file_landing')" />
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="frameImg" class="font-semibold">Obrázok rámu kamery</label>
                                <input type="file" @change="e => handleFileUpload(e, 'file_frame')" />
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
