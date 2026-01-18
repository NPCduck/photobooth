<script setup>
    import { defineProps } from 'vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

    const props = defineProps({
        event: Object,
    });

    function getPhotoUrl(photo) {
        return route('private.getPhotoUrl', {
            path: photo.path,
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
            </div>
        </template>
        <template #default>
            <div class="flex flex-col gap-4 w-full">
                <!-- Container - Detaily -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Šablóna
                    </p>
                    <div class="bg-overlaybg flex rounded-md">
                        
                    </div>
                </div>
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">
                        Fotky
                    </p>
                    <div class="bg-overlaybg flex rounded-md">
                        <div class="bg-overlaybg grid grid-cols-4 gap-4 p-4 rounded-md">
                            <div v-for="photo in props.event.photos" :key="photo.id" class="p-2 rounded-md flex flex-col items-center bg-white shadow">
                                <div class="w-full h-48 overflow-hidden rounded-md">
                                    <img :src="getPhotoUrl(photo)" alt="Fotka" class="w-full h-full object-cover">
                                </div>
                                <p class="mt-2 text-sm text-gray-700">{{ photo.guest?.email || 'Neznámy' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>