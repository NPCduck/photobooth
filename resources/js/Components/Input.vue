<script setup>
    import { ref, computed } from 'vue';

    const props=defineProps({
        id : String,
        label : String,
        modelValue : [String, Number],
        type : {type: String, default: 'text'},
        error : String
    });

    const emit = defineEmits(['update:modelValue']);
    const model = computed({
        get: () => props.modelValue,
        set: (val) => emit('update:modelValue', val),
    });
</script>

<template>
    <div class="rounded-md flex flex-col border border-sidebarbg p-2">
        <label :for="id" class="font-semibold">{{ label }}</label>
        <input
            :id="id"
            v-model="model"
            :type="type"
            class="border-0 bg-overlaybg rounded-md"
        />
        <p v-if="error" class="text-sm text-red-600 font-bold">
            {{ error }}
        </p>
    </div>
</template>