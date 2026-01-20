<script setup>
</script>

<template>
    <div
        class="
        flex flex-row gap-3
        md:flex-row md:items-center md:justify-between
        p-3 bg-itembg rounded-md"
    >
        <!-- LEFT SIDE -->
        <div
        class="
            flex flex-col gap-2
            md:flex-row md:items-center"
        >
            <!-- NAME -->
            <div
                class="
                md:border-r-2 md:border-gray-300
                md:pr-2
                font-medium text-lg"
            >
                <slot name="name" />
            </div>

            <!-- ATTRIBUTES -->
            <div
                class="
                flex flex-col gap-1
                text-sm text-gray-700
                md:pl-2 md:flex-row md:gap-4"
            >
                <slot name="attributes" />
            </div>
        </div>

        <!-- BUTTONS -->
        <div
        class="
            flex flex-row gap-2
            justify-end
            md:justify-start"
        >
            <slot name="buttons" />
        </div>
    </div>
</template>
