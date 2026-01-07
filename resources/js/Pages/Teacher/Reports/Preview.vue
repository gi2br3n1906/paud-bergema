<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    student: Object,
    academicTerm: Object,
    attendanceSummary: Object,
    latestGrowth: Object,
    assessments: Object
})

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const getAge = (dateOfBirth) => {
    if (!dateOfBirth) return '-'
    const today = new Date()
    const birth = new Date(dateOfBirth)
    let years = today.getFullYear() - birth.getFullYear()
    let months = today.getMonth() - birth.getMonth()

    if (months < 0) {
        years--
        months += 12
    }

    return `${years} tahun ${months} bulan`
}

const totalDays = computed(() => {
    return Object.values(props.attendanceSummary).reduce((sum, val) => sum + val, 0)
})

const getScoreBadge = (score) => {
    const badges = {
        'BB': 'bg-red-100 text-red-800',
        'MB': 'bg-yellow-100 text-yellow-800',
        'BSH': 'bg-blue-100 text-blue-800',
        'BSB': 'bg-green-100 text-green-800'
    }
    return badges[score] || 'bg-gray-100 text-gray-800'
}

const printReport = () => {
    window.print()
}
</script>

<template>
    <Head :title="`Raport - ${student.name}`" />

    <TeacherLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button @click="$inertia.visit(route('teacher.reports.index'))" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Raport {{ student.name }}</h1>
                        <p class="text-sm text-gray-500">{{ academicTerm.name }} - {{ academicTerm.academic_year.name }}</p>
                    </div>
                </div>
                <button
                    @click="printReport"
                    class="hidden md:inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors print:hidden"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Raport
                </button>
            </div>
        </template>

        <div class="space-y-6 print:space-y-4">
            <!-- Student Identity -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 print:shadow-none print:border-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base">Identitas Siswa</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-gray-500">Nama Lengkap</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ student.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">NISN</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ student.nisn }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Jenis Kelamin</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ student.gender }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tempat, Tanggal Lahir</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ student.place_of_birth }}, {{ formatDate(student.date_of_birth) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Usia</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ getAge(student.date_of_birth) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Kelas</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ student.classroom.name }} - {{ student.classroom.level }}</p>
                    </div>
                </div>
            </div>

            <!-- Attendance -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 print:shadow-none print:border-2 print:break-inside-avoid">
                <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base">Rekapitulasi Kehadiran</h3>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="text-center p-3 rounded-lg bg-green-50 border border-green-200">
                        <p class="text-xs text-green-600 font-medium">Hadir</p>
                        <p class="mt-1 text-2xl font-bold text-green-700">{{ attendanceSummary.Hadir || 0 }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                        <p class="text-xs text-yellow-600 font-medium">Sakit</p>
                        <p class="mt-1 text-2xl font-bold text-yellow-700">{{ attendanceSummary.Sakit || 0 }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-blue-50 border border-blue-200">
                        <p class="text-xs text-blue-600 font-medium">Izin</p>
                        <p class="mt-1 text-2xl font-bold text-blue-700">{{ attendanceSummary.Izin || 0 }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-red-50 border border-red-200">
                        <p class="text-xs text-red-600 font-medium">Alpa</p>
                        <p class="mt-1 text-2xl font-bold text-red-700">{{ attendanceSummary.Alpa || 0 }}</p>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 text-center">Total Hari: {{ totalDays }} hari</p>
            </div>

            <!-- Growth Record -->
            <div v-if="latestGrowth" class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 print:shadow-none print:border-2 print:break-inside-avoid">
                <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base">Data Tumbuh Kembang</h3>
                <p class="text-xs text-gray-500 mb-3">Pengukuran terakhir: {{ formatDate(latestGrowth.measurement_date) }}</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="text-center p-4 rounded-lg bg-blue-50 border border-blue-200">
                        <p class="text-xs text-blue-600 font-medium">Tinggi Badan</p>
                        <p class="mt-1 text-2xl font-bold text-blue-700">{{ latestGrowth.height }}</p>
                        <p class="text-xs text-blue-600">cm</p>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-green-50 border border-green-200">
                        <p class="text-xs text-green-600 font-medium">Berat Badan</p>
                        <p class="mt-1 text-2xl font-bold text-green-700">{{ latestGrowth.weight }}</p>
                        <p class="text-xs text-green-600">kg</p>
                    </div>
                    <div v-if="latestGrowth.head_circumference" class="text-center p-4 rounded-lg bg-purple-50 border border-purple-200">
                        <p class="text-xs text-purple-600 font-medium">Lingkar Kepala</p>
                        <p class="mt-1 text-2xl font-bold text-purple-700">{{ latestGrowth.head_circumference }}</p>
                        <p class="text-xs text-purple-600">cm</p>
                    </div>
                </div>
            </div>

            <!-- Assessments -->
            <div v-for="(categoryAssessments, category) in assessments" :key="category" class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 print:shadow-none print:border-2 print:break-inside-avoid">
                <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base">{{ category }}</h3>
                <div class="space-y-3">
                    <div v-for="assessment in categoryAssessments" :key="assessment.id" class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ assessment.assessment_aspect.name }}</p>
                            <p v-if="assessment.notes" class="mt-1 text-xs text-gray-600">{{ assessment.notes }}</p>
                        </div>
                        <span class="ml-3 inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-semibold" :class="getScoreBadge(assessment.score)">
                            {{ assessment.score }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="rounded-lg bg-gray-50 p-4 border border-gray-200 print:shadow-none print:border-2 print:break-inside-avoid">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Keterangan Penilaian:</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded bg-red-500 mr-2"></span>
                        <span>BB - Belum Berkembang</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded bg-yellow-500 mr-2"></span>
                        <span>MB - Mulai Berkembang</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded bg-blue-500 mr-2"></span>
                        <span>BSH - Berkembang Sesuai Harapan</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 rounded bg-green-500 mr-2"></span>
                        <span>BSB - Berkembang Sangat Baik</span>
                    </div>
                </div>
            </div>

            <!-- Print Button (Mobile) -->
            <div class="md:hidden print:hidden">
                <button
                    @click="printReport"
                    class="w-full inline-flex justify-center items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Raport
                </button>
            </div>
        </div>
    </TeacherLayout>
</template>

<style scoped>
@media print {
    @page {
        margin: 1cm;
    }
}
</style>
