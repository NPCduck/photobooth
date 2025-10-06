<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const isOpen = ref(false);
const toggleSidebar = () => (isOpen.value = !isOpen.value);

const page = usePage();
const currentRoute = computed(() => page.component);

const menuItems = ref([
  {
    label: 'Dashboard',
    route: 'dashboard',
    icon: 'dashboard-ico.png',
    children: [],
    open: false,
    selectedChild: null,
  },
  {
    label: 'Events',
    icon: 'events-ico.png',
    children: [{ label: 'Event manager', route: 'events.index' }],
    open: false,
    selectedChild: null,
  },
  {
    label: 'Profile',
    icon: 'profile-ico.png',
    children: [{ label: 'User profile', route: 'profile.edit' }],
    open: false,
    selectedChild: null,
  },
]);

menuItems.value.forEach(item => {
  if (item.children?.length) {
    item.children.forEach(child => {
      if (route().current(child.route)) {
        item.open = true;
        item.selectedChild = child.route;
      }
    });
  }
});

function toggleMenu(item) {
  // ak má children, len otvoríme/zavrieme dropdown
  if (item.children?.length) {
    item.open = !item.open
    if (item.open && !item.selectedChild) {
      item.selectedChild = item.children[0].route
    }
  }
}
function selectChild(item, child) {
  item.selectedChild = child.route
}
</script>

<template>
  <!-- mobile toggle -->
  <button
    @click="toggleSidebar"
    class="md:hidden fixed top-4 left-4 z-50 bg-indigo-600 text-white p-2 rounded-lg"
  >
    ☰
  </button>

  <!-- overlay -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 z-40 md:hidden"
    @click="toggleSidebar"
  ></div>

  <!-- sidebar -->
  <aside
    :class="[
      'fixed flex flex-col justify-between display-column md:sticky top-[15px] left-[15px] ml-5 h-[calc(100vh-30px)] bg-sidebarbg rounded-md shadow-md w-64 z-50 transform transition-transform duration-300 text-white',
      isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
    ]"
  >
    <div>
      <div class="p-4 font-bold text-center text-white text-lg">PhotoBooth</div>

      <nav class="p-4 ml-4 mr-4 space-y-2 bg-sidebarbg-dark rounded-md">
        <ul>
          <li v-for="item in menuItems" :key="item.label" class="mb-2">
            <!-- Ak má children → správa sa ako toggle -->
            <Link
              v-if="item.children?.length"
              :href=" route(item.children[0].route) "
              @click="toggleMenu(item)"
              class="w-full text-left px-3 py-2 rounded-md hover:bg-highlight flex justify-between items-center"
              :class="{
                'bg-highlight font-medium':
                  item.selectedChild && route().current(item.selectedChild),
              }"
            >
              <span>{{ item.label }}</span>
              <svg
                class="w-4 h-4 transition-transform duration-200"
                :class="{ 'rotate-90': item.open }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </Link>

            <!-- Ak nemá children → je to normálny Link -->
            <Link
              v-else
              :href="route(item.route)"
              class="block px-3 py-2 rounded-md hover:bg-highlight"
              :class="{
                'bg-highlight font-medium':
                  route().current(item.route),
              }"
            >
              {{ item.label }}
            </Link>

            <!-- Dropdown deti -->
            <ul
              v-if="item.children?.length && item.open"
              class="pl-5 mt-1 space-y-1"
            >
              <li
                v-for="child in item.children"
                :key="child.label"
                @click="selectChild(item, child)"
                class="px-3 py-2 rounded-md cursor-pointer"
                :class="{
                  'bg-highlight font-medium':
                    route().current(child.route),
                  'hover:bg-highlight':
                    !route().current(child.route),
                }"
              >
                <Link :href="route(child.route)">{{ child.label }}</Link>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
    <Button
      class="block bg-sidebarbg-dark w-32 rounded-md p-4 text-center ml-4 mb-4 hover:bg-highlight"
      @click="$inertia.post(route('logout'))"
    >
      Log out
  </Button>
  </aside>
</template>
