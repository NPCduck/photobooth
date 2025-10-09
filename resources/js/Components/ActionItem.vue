<script setup>

</script>

<template>
    <div class="flex flex-row p-3 rounded-md bg-itembg items-center">
        <div class="pr-2">
            <slot name="icon" />
        </div>
        <div class="flex flex-col">
            <slot name="name" />
            <slot name="ago" />
        </div>
    </div>
</template>