<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    event: Object,
    order: Object,
});

// Akcie pre objednávku
function markPaid() {
    Swal.fire({
        title: 'Označiť objednávku ako zaplatenú?',
        showCancelButton: true,
        confirmButtonText: 'Áno',
        icon: 'warning',
    }).then((result) => {
        if (!result.isConfirmed) return;

        router.post(route('events.orders.bulkAction', props.event.id), {
            action: 'mark_paid',
            order_ids: [props.order.id]
        }, { preserveScroll: true });
    });
}

function cancelOrder() {
    Swal.fire({
        title: 'Zrušiť objednávku?',
        showCancelButton: true,
        confirmButtonText: 'Áno',
        icon: 'warning',
    }).then((result) => {
        if (!result.isConfirmed) return;

        router.post(route('orders.cancel', props.order.id), {}, { preserveScroll: true });
    });
}

function getPhotoUrl(photo) {
    return route('private.getPhotoUrl', {
        path: photo.path
    });
}
</script>

<template>
<AuthenticatedLayout>
    <template #header>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl md:text-3xl font-normal text-gray-800">
                <b>Objednávka:</b> {{ order.code }}
            </h2>
            <div class="hidden gap-4 md:flex">
                <button @click="markPaid" class="px-4 py-2 bg-sidebarbg text-white rounded-md hover:bg-sidebarbg-dark">
                    Označiť zaplatené
                </button>
                <button @click="cancelOrder" class="px-4 py-2 bg-sidebarbg text-white rounded-md hover:bg-sidebarbg-dark">
                    Zrušiť
                </button>
            </div>
        </div>
    </template>

    <template #default>
        <div class="flex gap-4 justify-end md:hidden mb-4">
            <button @click="markPaid" class="px-4 py-2 bg-sidebarbg text-white rounded-md hover:bg-sidebarbg-dark">
                Označiť zaplatené
            </button>
            <button @click="cancelOrder" class="px-4 py-2 bg-sidebarbg text-white rounded-md hover:bg-sidebarbg-dark">
                Zrušiť
            </button>
        </div>
        <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">

            <!-- Základné info -->
            <div class="flex flex-col gap-2">
                <p><span class="font-semibold">Status:</span> {{ order.status }}</p>
                <p><span class="font-semibold">Suma:</span> {{ order.amount }} €</p>
                <p><span class="font-semibold">Kód objednávky:</span> {{ order.code }}</p>
                <p><span class="font-semibold">Nahraté fotky:</span> {{ order.guest.photos_uploaded }} / {{ order.guest.photo_limit == 0 ? 'neobmedzené' : order.guest.photo_limit }}</p>
            </div>

            <hr>

            <!-- Zákazník -->
            <div class="flex flex-col gap-2">
                <p class="font-semibold text-lg">Zákazník</p>
                <p><span class="font-semibold">Email:</span> {{ order.guest.email }}</p>
            </div>

            <hr>

            <!-- Balíček objednávky -->
            <div class="flex flex-col gap-2">
                <p class="font-semibold text-lg">Balíček</p>
                <div v-for="item in order.items" :key="item.id" class="p-2 bg-gray-50 rounded-md">
                    <p><span class="font-semibold">Názov:</span> {{ item.name }}</p>
                    <p><span class="font-semibold">Cena:</span> {{ item.unit_price }} €</p>
                    <p><span class="font-semibold">Spolu:</span> {{ item.total_price }} €</p>
                    <p><span class="font-semibold">Limit fotiek:</span> {{ order.guest.photo_limit == 0 ? 'neobmedzené' : order.guest.photo_limit }}</p>
                </div>
            </div>
            <!-- Fotky -->
            <div class="flex flex-col gap-2">
                <p class="font-semibold text-lg">Fotky</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-for="photo in order.guest.photos" :key="photo.id" class="bg-gray-100 rounded-md overflow-hidden flex items-center justify-center">
                        <img :src="getPhotoUrl(photo)" alt="Fotka" class="w-full aspect-square">
                    </div>
                </div>
            </div>
        </div>
    </template>
</AuthenticatedLayout>
</template>
