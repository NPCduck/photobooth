<script setup>
import Sidebar from '@/Components/Sidebar.vue';
</script>

<template>
    <div class=" flex min-h-screen bg-overlaybg">
        <div class="flex w-full min-h-screen p-[15px] gap-4">
            <Sidebar />
            
            <!-- Content wrap -->
            <div class="flex flex-col w-full gap-4">
                <!-- Page Heading -->
                <header class="rounded-md">
                    <div class=" font-thin">
                        <slot name="header" />
                    </div>
                </header>
                <!-- Page Content -->
                <main class="">
                    <slot />
                </main>
            </div>
            
        </div>
    </div>
</template>
