<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Eye, FileText, CheckCircle, X } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    event: Object,
});

const selectedOrders = ref([]);

// hromadné akcie
function bulkAction(action) {
    if (!selectedOrders.value.length) {
        Swal.fire('Vyberte objednávky', '', 'info');
        return;
    }

    Swal.fire({
        title: 'Naozaj chcete vykonať túto akciu?',
        showCancelButton: true,
        confirmButtonText: 'Áno',
        icon: 'warning',
    }).then((result) => {
        if (!result.isConfirmed) return;

        router.post(route('orders.bulkAction', { event: props.event.id }), {
            action,
            order_ids: selectedOrders.value,
        }, {
            preserveScroll: true,
        });
    });
}

// vyber/odvyber všetky
function toggleSelectAll(e) {
    if (e.target.checked) {
        selectedOrders.value = props.event.orders.map(o => o.id);
    } else {
        selectedOrders.value = [];
    }
}
</script>

<template>
<AuthenticatedLayout>
    <template #header>
        <div class="flex justify-between mb-4 items-center">
            <h2 class="text-3xl font-normal text-gray-800">Objednávky pre {{ event.name }}</h2>
        </div>
    </template>

    <template #default>
        <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
            <!-- Hromadné akcie -->
            <div class="flex gap-2 mb-4">
                <button @click="bulkAction('mark_paid')" class="px-4 py-2 bg-sidebarbg text-white rounded-md hover:bg-sidebarbg-dark">Označiť zaplatené</button>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th><input type="checkbox" @change="toggleSelectAll"/></th>
                        <th>Email</th>
                        <th>Balíček</th>
                        <th>Čiastka</th>
                        <th>Status</th>
                        <th>Akcie</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in event.orders" :key="order.id" class="border-t">
                        <td><input type="checkbox" v-model="selectedOrders" :value="order.id" /></td>
                        <td>{{ order.guest.email }}</td>
                        <td>{{ order.items[0]?.name }}</td>
                        <td>{{ order.amount }} €</td>
                        <td>{{ order.status }}</td>
                        <td class="flex gap-2">
                            <Link :href="route('events.orders.show', { event: event.id, order: order.id })">
                                <Eye class="text-gray-600 hover:text-gray-800"/>
                            </Link>
                            <!-- Link :href="route('orders.invoice', order)">
                                <FileText class="text-gray-600 hover:text-gray-800"/>
                            </Link -->
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="!event.orders.length" class="text-center py-10 text-gray-500">
                Žiadne objednávky
            </div>
        </div>
    </template>
</AuthenticatedLayout>
</template>
