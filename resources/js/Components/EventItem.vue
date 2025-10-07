<script setup>

</script>

<template>
    <div class="flex flex-row p-4 bg-">
        <div>
            <div>
                <slot name="name" />
            </div>
            <div>
                <slot name="attributes" />
            </div>
        </div>
        <div>
            <slot name="buttons" />
        </div>
    </div>
</template>