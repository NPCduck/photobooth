<script setup>

</script>

<template>
    <div class="flex flex-row p-3 justify-between bg-itembg">
        <div class="flex flex-row items-center">
            <div class="border-r-2  border-gray-300 pr-1">
                <slot name="name" />
            </div>
            <div class="pl-1 flex flex-row justify-between">
                <slot name="attributes" />
            </div>
        </div>
        <div class="flex flex-row gap-2">
            <slot name="buttons" />
        </div>
    </div>
</template>