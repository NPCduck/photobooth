<script setup>
    import { ref, computed, onMounted } from 'vue';
    import Input from '@/Components/Input.vue';

    import { Trash2 } from 'lucide-vue-next';

    const props = defineProps({
        model : [Object],
    });

    const inputRef = ref(null);
    const checkBoxRef = ref(null);
    const readonly = ref(false);

    const emits = defineEmits(['remove']);
    const id = props.model.id;

    onMounted(() => {
        if(inputRef && checkBoxRef) {
            checkBoxRef.value.addEventListener('change', (e) => {
                readonly.value = checkBoxRef.value.checked;
                inputRef.value.value = 0;
            });
        }
    });
</script>

<template>
    <div class="relative grid grid-cols-4 gap-4 items-start packages mr-8">
        <Input :id="'packageType-' + id" label="Typ balíčka" :modelValue="model.type" />
        <Input :id="'packagePrice-' + id" label="Cena balíčka €" :modelValue="model.price" />
        <Input :id="'packagePhotoLimitTotal' + id" label="Celkový limit fotiek" :modelValue="model.type" />
        <div class="flex flex-col p-2 gap-2 border border-sidebarbg rounded-md">
            <div class="flex flex-col">
                <label :for="'packagePhotoLimitPerson-' + id" class="font-semibold">Počet fotiek na osobu</label>
                <input type="text" :id="'packagePhotoLimitPerson-' + id" :readonly="readonly" :placeholder="readonly ? 0 : ''" v-model="model.photoLimitPerson" ref="inputRef" class="rounded-md border-0 bg-overlaybg">
            </div>
            <div class="flex flex-row items-center gap-2">
                <input type="checkbox" :id="'packageUnlimitedPhotosPerson-' + id" ref="checkBoxRef" class="bg-overlaybg border-0 rounded-md">
                <label :for="'packageUnlimitedPhotosPerson-' + id">Neobmedzený počet</label>
            </div>
        </div>
        <div v-if="id !== 1" class="absolute top-[50%] translate-y-[-50%] right-[-40px] bg-red-600 rounded-md p-1 hover:bg-red-700 flex items-center">
            <button
                type="button"
                @click="$emit('remove')"
            >
                <Trash2 class="text-white translate-y-[-1px]" />
            </button>
        </div>
    </div>
</template>