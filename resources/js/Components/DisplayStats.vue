<script setup>

</script>

<template>
    <div class="flex flex-1 flex-row items-center gap-4 p-4 bg-white rounded-md shadow-md text-text h-16">
        <div>
            <slot name="icon" />
        </div>

        <div>
            <div>
                <slot name="value" />
            </div>

            <div>
                <slot />
            </div>
        </div>
    </div>
</template>