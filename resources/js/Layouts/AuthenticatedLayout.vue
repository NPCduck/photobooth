<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
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
