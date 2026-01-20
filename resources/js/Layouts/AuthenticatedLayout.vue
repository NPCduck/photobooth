<script setup>
import { ref } from 'vue'
import Sidebar from '@/Components/Sidebar.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="min-h-screen bg-overlaybg md:pt-4">

    <!-- MOBILE HEADER -->
    <div class="md:hidden flex items-baseline gap-3 p-4">
      <button
        @click="sidebarOpen = true"
        class="bg-sidebarbg text-white p-2 rounded-lg"
      >
        ☰
      </button>
      <slot name="header" />
    </div>

    <!-- LAYOUT -->
    <div class="flex gap-4 p-[15px] pt-0">

      <Sidebar
        :isOpen="sidebarOpen"
        @close="sidebarOpen = false"
      />

      <!-- CONTENT -->
      <div class="flex flex-col w-full gap-4">
        <header class="hidden md:block">
          <slot name="header" />
        </header>

        <main>
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
