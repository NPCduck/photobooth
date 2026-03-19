<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Users, Calendar } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DisplayStats from '@/Components/DisplayStats.vue';

const props = defineProps({
	events: Array,
    users: Array,
});

const usersCount = computed(() => props.users?.length || 0);
const eventsCount = computed(() => props.events?.length || 0);

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin);
</script>

<template>
	<AuthenticatedLayout>
		<template #header>
			<h2 class="text-2xl md:text-3xl mb-4 font-normal text-gray-800">
				Admin Nástenka
			</h2>
		</template>

		<template #default>
			<div class="flex flex-col gap-6 w-full">
				<!-- STATS -->
				<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
					<DisplayStats>
						<template #icon>
							<Users />
						</template>
						<template #value>{{ usersCount }}</template>
						Počet používateľov
					</DisplayStats>
					<DisplayStats>
						<template #icon>
							<Calendar />
						</template>
						<template #value>{{ eventsCount }}</template>
						Počet eventov
					</DisplayStats>
				</div>

				<!-- USERS TABLE -->
				<div class="bg-white p-4 md:p-6 shadow rounded-md">
					<h3 class="font-thin text-xl md:text-2xl mb-4">Používatelia</h3>
					<div class="overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
									<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meno</th>
									<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
									<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr v-for="user in users" :key="user.id">
									<td class="px-4 py-2">{{ user.id }}</td>
									<td class="px-4 py-2">{{ user.name }}</td>
									<td class="px-4 py-2">{{ user.email }}</td>
									<td class="px-4 py-2">
										<span v-if="user.is_admin" class="text-green-600 font-bold">✔</span>
										<span v-else class="text-gray-400">—</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</template>
	</AuthenticatedLayout>
</template>
