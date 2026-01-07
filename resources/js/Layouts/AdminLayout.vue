<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

const page = usePage()
const sidebarOpen = ref(false)

const user = computed(() => page.props.auth.user)

const navigation = [
    {
        name: 'Dashboard',
        href: route('admin.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        current: route().current('admin.dashboard')
    },
    {
        name: 'Tahun Ajaran',
        href: route('admin.academic-years.index'),
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        current: route().current('admin.academic-years.*')
    },
    {
        name: 'Kelas',
        href: route('admin.classrooms.index'),
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        current: route().current('admin.classrooms.*')
    },
    {
        name: 'Siswa',
        href: route('admin.students.index'),
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        current: route().current('admin.students.*')
    },
    {
        name: 'Guru',
        href: route('admin.teachers.index'),
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        current: route().current('admin.teachers.*')
    },
    {
        name: 'Aspek Penilaian',
        href: route('admin.assessment-aspects.index'),
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        current: route().current('admin.assessment-aspects.*')
    }
]
</script>

<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar for desktop -->
        <div class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex w-64 flex-col">
                <div class="flex min-h-0 flex-1 flex-col border-r border-gray-200 bg-white">
                    <!-- Sidebar header -->
                    <div class="flex flex-1 flex-col overflow-y-auto pt-5 pb-4">
                        <div class="flex flex-shrink-0 items-center px-4">
                            <ApplicationLogo class="h-8 w-auto" />
                            <span class="ml-2 text-xl font-semibold text-gray-800">Unit PAUD</span>
                        </div>
                        <!-- Navigation -->
                        <nav class="mt-8 flex-1 space-y-1 bg-white px-2">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    item.current
                                        ? 'bg-blue-50 border-blue-500 text-blue-700'
                                        : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                    'group flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors duration-150'
                                ]"
                            >
                                <svg
                                    :class="[
                                        item.current ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                                        'mr-3 h-6 w-6 flex-shrink-0'
                                    ]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </nav>
                    </div>
                    <!-- Sidebar footer -->
                    <div class="flex flex-shrink-0 border-t border-gray-200 p-4">
                        <Dropdown align="top" width="48">
                            <template #trigger>
                                <button class="group block w-full flex-shrink-0">
                                    <div class="flex items-center">
                                        <div class="inline-block h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-700">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <div class="ml-3 text-left">
                                            <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                                {{ user.name }}
                                            </p>
                                            <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                                Administrator
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div class="lg:hidden">
            <div
                v-show="sidebarOpen"
                class="fixed inset-0 z-40 flex"
                @click="sidebarOpen = false"
            >
                <div class="fixed inset-0">
                    <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
                </div>
                <div
                    class="relative flex w-full max-w-xs flex-1 flex-col bg-white"
                    @click.stop
                >
                    <div class="absolute top-0 right-0 -mr-12 pt-2">
                        <button
                            @click="sidebarOpen = false"
                            class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        >
                            <span class="sr-only">Close sidebar</span>
                            <svg
                                class="h-6 w-6 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="h-0 flex-1 overflow-y-auto pt-5 pb-4">
                        <div class="flex flex-shrink-0 items-center px-4">
                            <ApplicationLogo class="h-8 w-auto" />
                            <span class="ml-2 text-xl font-semibold text-gray-800">Unit PAUD</span>
                        </div>
                        <nav class="mt-5 space-y-1 px-2">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    item.current
                                        ? 'bg-blue-50 border-blue-500 text-blue-700'
                                        : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                    'group flex items-center px-3 py-2 text-base font-medium border-l-4'
                                ]"
                            >
                                <svg
                                    :class="[
                                        item.current ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                                        'mr-4 h-6 w-6 flex-shrink-0'
                                    ]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </nav>
                    </div>
                    <div class="flex flex-shrink-0 border-t border-gray-200 p-4">
                        <div class="group block flex-shrink-0">
                            <div class="flex items-center">
                                <div class="inline-block h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-base font-medium text-blue-700">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-gray-700">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-sm font-medium text-gray-500">Administrator</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-14 flex-shrink-0"></div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top bar -->
            <div class="relative z-10 flex h-16 flex-shrink-0 border-b border-gray-200 bg-white shadow-sm">
                <button
                    @click="sidebarOpen = true"
                    class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 lg:hidden"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </button>
                <div class="flex flex-1 justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-1 items-center">
                        <!-- Page title slot -->
                        <h1 v-if="$slots.header" class="text-xl font-semibold text-gray-900">
                            <slot name="header" />
                        </h1>
                    </div>
                    <div class="ml-4 flex items-center lg:ml-6">
                        <!-- User menu for desktop -->
                        <div class="hidden lg:block">
                            <span class="text-sm text-gray-700">{{ user.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
