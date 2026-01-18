<script setup>
    import { router } from '@inertiajs/vue3'

    const props = defineProps({
        token: String,
        guest: Object,
    });
</script>

<template>
    <div class="text-center space-y-6">
        <h1 class="text-2xl font-bold">Ďakujeme za fotku 📸</h1>

        <button
            class="btn"
            @click="router.visit(route('capture.show', props.token))"
        >
            Nahrať ďalšiu fotku
        </button>

        <p class="text-gray-500">
            Môžete zavrieť túto stránku
        </p>
    </div>
</template>
