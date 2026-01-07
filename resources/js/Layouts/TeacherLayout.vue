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
        href: route('teacher.dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        current: route().current('teacher.dashboard')
    },
    {
        name: 'Log Harian',
        href: route('teacher.daily-logs.index'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        current: route().current('teacher.daily-logs.*')
    },
    {
        name: 'Tumbuh Kembang',
        href: route('teacher.growth-records.index'),
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        current: route().current('teacher.growth-records.*')
    },
    {
        name: 'Penilaian',
        href: route('teacher.assessments.index'),
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        current: route().current('teacher.assessments.*')
    },
    {
        name: 'Raport',
        href: route('teacher.reports.index'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        current: route().current('teacher.reports.*')
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

                        <!-- User info -->
                        <div class="mt-6 px-4">
                            <div class="rounded-lg bg-blue-50 p-3">
                                <p class="text-xs font-medium text-blue-600">Guru</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">{{ user.name }}</p>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <nav class="mt-6 flex-1 space-y-1 bg-white px-2">
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
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="item.icon"
                                    />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </nav>
                    </div>

                    <!-- User dropdown -->
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
                                                Guru
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 flex lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>

            <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button
                        type="button"
                        class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        @click="sidebarOpen = false"
                    >
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                'group flex items-center px-3 py-2 text-sm font-medium border-l-4'
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
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top bar for mobile -->
            <div class="lg:hidden">
                <div class="flex items-center justify-between bg-white px-4 py-3 shadow">
                    <button
                        type="button"
                        class="-ml-0.5 -mt-0.5 inline-flex h-12 w-12 items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none"
                        @click="sidebarOpen = true"
                    >
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="text-lg font-semibold text-gray-900">
                        <slot name="header" />
                    </div>
                    <div class="w-12"></div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto focus:outline-none">
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <!-- Page header (desktop only) -->
                        <div class="hidden lg:block mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900">
                                <slot name="header" />
                            </h1>
                        </div>

                        <!-- Page content -->
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
