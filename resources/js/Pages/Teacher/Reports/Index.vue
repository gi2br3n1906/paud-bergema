<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    classrooms: Array,
    academicTerms: Array
})

const selectedClassroomId = ref(null)
const selectedTermId = ref(null)

const handleViewReports = () => {
    if (!selectedClassroomId.value || !selectedTermId.value) {
        alert('Pilih kelas dan semester terlebih dahulu')
        return
    }

    router.visit(route('teacher.reports.students', {
        classroom: selectedClassroomId.value,
        term: selectedTermId.value
    }))
}
</script>

<template>
    <Head title="Raport Siswa" />

    <TeacherLayout>
        <template #header>
            Raport Siswa
        </template>

        <div class="space-y-6">
            <!-- Selection Card -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Pilih Kelas dan Semester</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Pilih kelas dan semester untuk melihat raport siswa
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Kelas
                        </label>
                        <select
                            v-model="selectedClassroomId"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option :value="null" disabled>Pilih kelas...</option>
                            <option
                                v-for="classroom in classrooms"
                                :key="classroom.id"
                                :value="classroom.id"
                            >
                                {{ classroom.name }} - {{ classroom.level }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Semester
                        </label>
                        <select
                            v-model="selectedTermId"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option :value="null" disabled>Pilih semester...</option>
                            <option
                                v-for="term in academicTerms"
                                :key="term.id"
                                :value="term.id"
                            >
                                {{ term.name }} - {{ term.academic_year.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button
                        @click="handleViewReports"
                        :disabled="!selectedClassroomId || !selectedTermId"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Lihat Raport
                    </button>
                </div>
            </div>

            <!-- Info Card -->
            <div class="rounded-lg bg-blue-50 p-6 border border-blue-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Tentang Raport</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Raport siswa berisi informasi lengkap meliputi:</p>
                            <ul class="mt-2 space-y-1 list-disc list-inside">
                                <li>Data pribadi siswa</li>
                                <li>Rekapitulasi kehadiran</li>
                                <li>Data tumbuh kembang (tinggi, berat, lingkar kepala)</li>
                                <li>Penilaian perkembangan per aspek</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
