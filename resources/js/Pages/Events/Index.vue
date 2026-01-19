<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import EventItem from '@/Components/EventItem.vue'
    import { Link, router } from '@inertiajs/vue3';
    import { CalendarPlus, Eye, Trash2, Pencil, Images, X } from 'lucide-vue-next'
    import Swal from 'sweetalert2';
    import { ref, watch } from 'vue';

    const props = defineProps({
        events: Array,
        filters: {
            type: Object,
            default: () => ({}),
        },
    });

    const search = ref(props.filters.search ?? '');
    const status = ref(props.filters.status ?? '');
    const sort   = ref(props.filters.sort   ?? '');


    let timeout = null;

    watch([search, status, sort], () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            router.get(route('events.index'), {
                search: search.value,
                status: status.value,
                sort: sort.value,
            }, {
                preserveState: true,
                replace: true,
            });
        }, 300);
    });

    function clearFilters() {
        search.value = '';
        status.value = '';
        sort.value = '';
    }

    function deleteEvent(id) {
        Swal.fire({
            title : 'Naozaj chcete vymazať tento event?',
            showCancelButton : true,
            confirmButtonText : 'Vymazať',
            icon: 'warning',
        }).then(result => {
            if (!result.isConfirmed) return;

            router.delete(route('events.destroy', id), {
                preserveScroll: true,
            });
        });
    }
</script>


<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-row justify-between mb-4">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    List eventov
                </h2>
                <Link
                    :href="route('events.create')"
                    class="text-white bg-sidebarbg rounded-md flex flex-row p-2 gap-2 hover:bg-sidebarbg-dark"
                >
                    <CalendarPlus />
                    <span>Vytvoriť event</span>
                </Link>
            </div>
        </template>
        <template #default>
            <div>
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <div class="flex flex-row justify-between items-center">
                        <p class="font-thin text-[25px]">
                        Zoznam eventov
                        </p>
                        <!-- Search -->
                        <div class="flex flex-col gap-4 md:flex-row">
                            <div class="flex flex-col">
                                <label class="text-sm text-gray-600">Vyhľadávanie</label>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Názov eventu..."
                                    class="border rounded-md px-3 py-2"
                                />
                            </div>

                            <!-- Status -->
                            <div class="flex flex-col">
                                <label class="text-sm text-gray-600">Stav</label>
                                <select v-model="status" class="border rounded-md px-3 py-2">
                                    <option value="">Všetky</option>
                                    <option value="aktuálny">Aktuálny</option>
                                    <option value="ukončený">Ukončený</option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div class="flex flex-col">
                                <label class="text-sm text-gray-600">Zoradenie</label>
                                <select v-model="sort" class="border rounded-md pr-8 px-3 py-2">
                                    <option value="">Najnovšie</option>
                                    <option value="name_asc">Názov ↑</option>
                                    <option value="name_desc">Názov ↓</option>
                                    <option value="date_asc">Dátum ↑</option>
                                    <option value="date_desc">Dátum ↓</option>
                                </select>
                            </div>

                            <!-- Clear -->
                            <button
                                v-if="search || status || sort"
                                @click="clearFilters"
                                class="text-sm text-red-600 flex items-center gap-1 mt-2 md:mt-0"
                            >
                                <X size="16" /> Zrušiť filtre
                            </button>
                        </div>
                    </div>
                    <ul>
                        <div v-if="events.length" class="flex flex-col gap-4">
                            <EventItem
                                v-for="event in events"
                                :key="event.name"
                                class="rounded-md align-center flex"
                            >
                                <template #name>
                                    <span class="text-xl truncate w-32 inline-block">{{ event.name }}</span>
                                </template>

                                <template #attributes>
                                    <div class="flex flex-row gap-2">
                                        <div>
                                            <span><b>Lokácia:</b></span>
                                            {{ event.details.loc_venue }}
                                        </div>
                                        <div>
                                            <span><b>Dátum:</b></span>
                                            {{ event.details.date }}
                                        </div>
                                        <div>
                                            <span><b>Obraty:</b></span>
                                            {{ event.orders.filter(order => order.status === 'completed').reduce((sum, order) => sum + order.amount, 0) }} €
                                        </div>
                                        
                                    </div>
                                </template>
                                
                                <template #buttons>
                                    <Link
                                        :href="route('events.show', event)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Eye />
                                        </div>
                                    </Link>
                                    <Link
                                        :href="route('events.photos', event)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Images />
                                        </div>
                                    </Link>
                                    <Link
                                        :href="route('events.edit', event)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Pencil />
                                        </div>
                                    </Link>
                                    <button
                                        @click="deleteEvent(event.id)"
                                    >
                                        <div class="hover:bg-itembg rounded-md p-1">
                                            <Trash2 />
                                        </div>
                                    </button>
                                </template>
                            </EventItem>
                        </div>
                        
                        <div v-else>
                            <h2 class="text-[20px]">
                                Neboli nájdené žiadne výsledky
                            </h2>
                        </div>
                    </ul>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
