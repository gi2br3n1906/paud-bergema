<script setup>
import { Head, Link } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

defineProps({
    classrooms: Array,
    stats: Object
})
</script>

<template>
    <Head title="Dashboard Guru" />

    <TeacherLayout>
        <template #header>
            Dashboard
        </template>

        <div class="space-y-6">
            <!-- Welcome Card -->
            <div class="rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white shadow-lg">
                <h2 class="text-2xl font-bold">Selamat Datang, Guru!</h2>
                <p class="mt-2 text-blue-100">
                    Kelola kelas dan siswa Anda dengan mudah
                </p>
            </div>

            <!-- Statistics Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Total Classes -->
                <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                                <svg
                                    class="h-6 w-6 text-blue-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500">
                                    Total Kelas
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                    {{ stats.total_classes }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Total Students -->
                <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                                <svg
                                    class="h-6 w-6 text-green-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500">
                                    Total Siswa
                                </dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                    {{ stats.total_students }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Active Academic Year -->
                <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                                <svg
                                    class="h-6 w-6 text-yellow-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500">
                                    Tahun Ajaran Aktif
                                </dt>
                                <dd class="mt-1 text-xl font-semibold text-gray-900">
                                    {{ stats.active_academic_year }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classrooms List -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kelas yang Diampu</h3>

                <div v-if="classrooms.length === 0" class="p-12 text-center">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">Belum ada kelas</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Anda belum ditugaskan untuk mengajar kelas apapun.
                    </p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="classroom in classrooms"
                        :key="classroom.id"
                        class="rounded-lg border border-gray-200 p-5 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ classroom.name }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ classroom.level }}
                                </p>
                            </div>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                                {{ classroom.students_count }} siswa
                            </span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button
                                class="rounded-lg border border-blue-300 bg-blue-50 px-4 py-3 text-center text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                            >
                                <svg
                                    class="mx-auto h-5 w-5 mb-1"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Log Harian
                            </button>

                            <button
                                class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700 hover:bg-green-100 transition-colors"
                            >
                                <svg
                                    class="mx-auto h-5 w-5 mb-1"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                Tumbuh Kembang
                            </button>
                        </div>

                        <div class="mt-3">
                            <button
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Lihat Daftar Siswa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
