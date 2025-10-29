<script setup>
    import { ref, watch } from 'vue';
    import Input from '@/Components/Input.vue';
    import { Trash2 } from 'lucide-vue-next';

    const props = defineProps({
        model : [Object],
        errors: {
            type: Object,
            default: () => ({})
        },
        isFirst: Boolean,
    });

    const readonly = ref(false);

    const emits = defineEmits(['remove']);
    const id = props.model.id;

    watch(readonly, (newVal) => {
        if (newVal) {
            props.model.photo_limit_person = 0;
        }
    });
</script>

<template>
    <div class="relative grid grid-cols-4 gap-4 items-start packages mr-8">
        <Input :id="'packageType-' + id" label="Typ balíčka" v-model="model.name" :error="errors.name"/>
        <Input type="number" :id="'packagePrice-' + id" label="Cena balíčka €" v-model="model.price" :error="errors.price"/>
        <Input type="number" :id="'packagePhotoLimitTotal' + id" label="Celkový limit fotiek" v-model="model.photo_limit_total" :error="errors.photo_limit_total"/>
        <div class="flex flex-col p-2 gap-2 border border-sidebarbg rounded-md">
            <div class="flex flex-col">
                <label :for="'packagePhotoLimitPerson-' + id" class="font-semibold">Počet fotiek na osobu</label>
                <input type="number" :id="'packagePhotoLimitPerson-' + id" :readonly="readonly" v-model="model.photo_limit_person" ref="inputRef" class="rounded-md border-0 bg-overlaybg">
            </div>
            <div class="flex flex-row items-center gap-2">
                <input type="checkbox" :id="'packageUnlimitedPhotosPerson-' + id" v-model="readonly" ref="checkBoxRef" class="bg-overlaybg border-0 rounded-md">
                <label :for="'packageUnlimitedPhotosPerson-' + id">Neobmedzený počet</label>
            </div>
        </div>
        <div v-if="!isFirst" class="absolute top-[50%] translate-y-[-50%] right-[-40px] bg-red-600 rounded-md p-1 hover:bg-red-700 flex items-center">
            <button
                type="button"
                @click="$emit('remove')"
            >
                <Trash2 class="text-white translate-y-[-1px]" />
            </button>
        </div>
    </div>
</template>