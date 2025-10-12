<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Input from '@/Components/Input.vue';
import Package from '@/Components/Package.vue';

import VueDatepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref, reactive } from 'vue';

const today = new Date();
today.setHours(0, 0, 0, 0);
const minDate = ref(today);

const form = useForm({
  details: {
    date: null,
    type: '',
    time_start: null,
    time_end: null,
    status: '',
    hosts: null,
    loc_venue: '',
    loc_address: '',
  },
  event: {
    name: '',
  },
  packages: reactive([]),
  client: {
    name: '',
    email: '',
    phone: '',
  },
  overlays: {
    landing_img: null,
    frame_img: null,
  },
  client: {
    name: '',
    email: '',
    phone: '',
  }
  
});

const statuses = [
    {title : 'aktuálny'},
    {title : 'ukončený'}
]

const submit = () => {
    form.post(route('events.store'), {
        onSuccess: () => {
            form.reset();
            console.log('Successfully sent!');
        },
    });
}

const dateFormat = () => {
    if (!form.details.date) return ''
    return form.details.date.toLocaleDateString('sk-SK');
}

const handleFileUpload = function(overlay) {
    overlay = e.target.files[0];
}

let packageId = 1;

const generatePackage = () => ({
    id : packageId++,
    type : '',
    cost : null,
    photoLimitTotal : null,
    photoLimitPerson : null,
});

const addPackage = () => {
    form.packages.push(generatePackage());
}

const removePackage = (index) => {
    form.packages.splice(index, 1);
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
            <form @submit.prevent="submit">
                <div class="flex flex-col gap-4 w-full">
                    <!-- Basic info -->
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Základné informácie
                        </p>
                        <div class="grid grid-cols-4 gap-4">
                            <Input id="name" label="Názov eventu" :modelValue="form['event.name']" :error="form.errors['event.name']" />
                            <Input id="type" label="Typ eventu" :modelValue="form['details.type']" :error="form.errors['details.type']" />
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
                                <p v-if="error">{{ form.errors['details.date'] }}</p>

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
                                    <span v-for="status in statuses" :key="status.title">
                                        <option :value="status.title">{{ status.title }}</option>
                                    </span>
                                </select>
                            </div>
                            <Input id="hosts" label="Počet očakávaných hostí" type="number" :modelValue="form['details.hosts']"  :error="form.errors['details.hosts']" />
                            
                        </div>
                    </div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Lokácia a detaily
                        </p>
                        <div class="grid grid-cols-2 gap-4 items-start">
                            <Input id="loc_venue" label="Miesto konania" :modelValue="form['details.loc_venue']" :error="form.errors['details.loc_venue']" />
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="status" class="font-semibold">Celá adresa</label>
                                <textarea id="loc_address" v-model="form['details.loc_address']" class="border-0 rounded-md bg-overlaybg h-32"></textarea>
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
                            <Input id="clientName" label="Meno klienta" :modelValue="form['client.name']" :error="form.errors['client.name']" />
                            <Input id="clientEmail" label="Email klienta" :modelValue="form['client.email']" :error="form.errors['client.email']" />
                            <Input id="clientPhone" label="Tel. č. klienta" :modelValue="form['client.phone']" :error="form.errors['client.phone']" />
                        </div>
                    </div>
                    <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                        <p class="font-thin text-[25px]">
                            Dodatočné nastavenia
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="landingImg" class="font-semibold">Obrázok hlavnej stránky</label>
                                <input type="file" @change="handleFileUpload(form.overlays.landing_img)" />
                            </div>
                            <div class="flex flex-col p-2 border border-sidebarbg rounded-md">
                                <label for="frameImg" class="font-semibold">Obrázok rámu kamery</label>
                                <input type="file" @change="handleFileUpload(form.overlays.frame_img)" />
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
