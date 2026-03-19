<script setup>

import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import {
  LayoutDashboard,
  Calendar,
  User,
  LogOut,
  Shield,
} from 'lucide-vue-next'

defineProps({
  isOpen: Boolean,
})
defineEmits(['close'])


const page = usePage();
const isAdmin = page.props.auth?.user?.is_admin;

const menuItems = [
  {
    label: 'Nástenka',
    route: 'dashboard',
    icon: LayoutDashboard,
  },
  {
    label: 'Eventy',
    icon: Calendar,
    children: [
      { label: 'Zoznam eventov', route: 'events.index' },
    ],
  },
  {
    label: 'Profil',
    icon: User,
    children: [
      { label: 'Spravovať účet', route: 'profile.edit' },
    ],
  },
  // Admin section (hidden for non-admins)
  ...(isAdmin ? [
    {
      label: 'Admin',
      icon: Shield,
      children: [
        { label: 'Admin Dashboard', route: 'admin.dashboard' },
        { label: 'Používatelia', route: 'admin.users.index' },
      ],
    },
  ] : []),
]

const isActive = (name) => route().current(name)
</script>

<template>
  <!-- Overlay (mobile) -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 z-40 md:hidden"
    @click="$emit('close')"
  />

  <!-- Sidebar -->
  <aside
    :class="[
      'fixed md:sticky',
      'top-[15px]',
      'left-[15px]',
      'h-[calc(100vh-30px)]',
      'w-72',
      'bg-sidebarbg text-white',
      'rounded-md shadow-md',
      'z-50',
      'transform transition-transform duration-300',
      isOpen ? 'translate-x-0' : '-translate-x-[120%] md:translate-x-0',
    ]"
  >
    <div class="flex flex-col h-full justify-between">

      <!-- Logo -->
      <div>
        <div class="p-4 text-center text-[25px] font-bold">
          <img src="/photobooth_w.svg" alt="">
        </div>

        <!-- Nav -->
        <nav class="mx-4 p-4 bg-sidebarbg-dark rounded-md space-y-2">
          <ul class="flex flex-col gap-2">
            <li v-for="item in menuItems" :key="item.label" class="flex flex-col gap-1">
              <Link
                v-if="item.children"
                :href="route(item.children[0].route)"
                class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-highlight"
                :class="{ 'bg-highlight font-medium': isActive(item.children[0].route) }"
                @click="$emit('close')"
              >
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.label }}
              </Link>

              <ul v-if="item.children" class="ml-6 mt-1 space-y-1 flex flex-col gap-1">
                <li v-for="child in item.children" :key="child.route">
                  <Link
                    :href="route(child.route)"
                    class="block px-3 py-2 text-sm rounded-md hover:bg-highlight"
                    :class="{ 'bg-highlight font-medium': isActive(child.route) }"
                    @click="$emit('close')"
                  >
                    {{ child.label }}
                  </Link>
                </li>
              </ul>

              <Link
                v-else
                :href="route(item.route)"
                class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-highlight"
                :class="{ 'bg-highlight font-medium': isActive(item.route) }"
                @click="$emit('close')"
              >
                <component :is="item.icon" class="w-5 h-5" />
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Logout -->
      <button
        class="mx-4 mb-4 flex items-center gap-2 bg-sidebarbg-dark hover:bg-highlight p-3 rounded-md"
        @click="$inertia.post(route('logout'))"
      >
        <LogOut class="w-5 h-5" />
        Odhlásiť sa
      </button>
    </div>
  </aside>
</template>
