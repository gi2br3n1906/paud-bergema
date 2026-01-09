<script setup>
import { Head, Link } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    classroom: Object,
    academicTerm: Object,
    students: Array
})
</script>

<template>
    <Head :title="`Raport - ${classroom.name}`" />

    <TeacherLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('teacher.reports.index')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ classroom.name }}</h1>
                    <p class="text-sm text-gray-500">{{ academicTerm.name }} - {{ academicTerm.academic_year.name }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Students List -->
            <div class="rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Siswa</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Klik siswa untuk melihat raport lengkap
                    </p>
                </div>

                <div v-if="students.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">Tidak ada siswa di kelas ini</h3>
                    <p class="mt-2 text-sm text-gray-500">Kelas ini belum memiliki siswa yang terdaftar.</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="student in students"
                        :key="student.id"
                        class="px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ student.name }}</h4>
                                    <p class="text-xs text-gray-500">NISN: {{ student.nisn }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Link
                                    :href="route('teacher.reports.assess', { student: student.id, term: academicTerm.id })"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Input Penilaian
                                </Link>

                                <Link
                                    :href="route('teacher.reports.preview', { student: student.id, term: academicTerm.id })"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Lihat Raport
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
