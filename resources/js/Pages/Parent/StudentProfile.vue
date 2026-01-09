<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
});

const logout = () => {
    router.post(route('parent.logout'));
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getStatusBadge = (status) => {
    return status === 'published'
        ? 'bg-green-100 text-green-800'
        : 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    return status === 'published' ? 'Sudah Dipublish' : 'Draft';
};
</script>

<template>
    <Head :title="`Profil ${student.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <Link :href="route('parent.dashboard')" class="mr-4">
                            <svg class="h-6 w-6 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <div class="bg-blue-600 text-white rounded-full p-2 mr-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Profil Anak</h1>
                            <p class="text-sm text-gray-500">{{ student.name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ $page.props.auth.user.name }}</p>
                            <p class="text-xs text-gray-500">Wali Murid</p>
                        </div>
                        <button
                            @click="logout"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 sm:px-0">
                <!-- Student Info Card -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-start space-x-6">
                        <div class="flex-shrink-0">
                            <div v-if="student.photo_url" class="h-24 w-24 rounded-full overflow-hidden ring-4 ring-blue-100">
                                <img :src="student.photo_url" :alt="student.name" class="h-full w-full object-cover">
                            </div>
                            <div v-else class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-3xl font-bold ring-4 ring-blue-100">
                                {{ student.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900">{{ student.name }}</h2>
                            <p v-if="student.nickname" class="text-lg text-gray-500 mb-4">"{{ student.nickname }}"</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <p class="text-sm text-gray-600">NISN</p>
                                    <p class="font-medium text-gray-900">{{ student.nisn || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Jenis Kelamin</p>
                                    <p class="font-medium text-gray-900">{{ student.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Tempat, Tanggal Lahir</p>
                                    <p class="font-medium text-gray-900">{{ student.place_of_birth || '-' }}, {{ formatDate(student.date_of_birth) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Kelas</p>
                                    <p class="font-medium text-gray-900">{{ student.classroom?.name || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Tanggal Masuk</p>
                                    <p class="font-medium text-gray-900">{{ formatDate(student.enrollment_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status</p>
                                    <p class="font-medium text-gray-900">{{ student.status }}</p>
                                </div>
                                <div class="md:col-span-2" v-if="student.address">
                                    <p class="text-sm text-gray-600">Alamat</p>
                                    <p class="font-medium text-gray-900">{{ student.address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Cards Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Raport
                    </h3>

                    <div v-if="student.report_cards.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Belum ada raport</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="report in student.report_cards"
                            :key="report.id"
                            class="border rounded-lg p-4 hover:border-blue-300 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">
                                        Raport {{ report.academic_term.semester }} - {{ report.academic_term.academic_year }}
                                    </h4>
                                    <div class="mt-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusBadge(report.status)"
                                        >
                                            {{ getStatusText(report.status) }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="report.status === 'published'" class="flex space-x-2">
                                    <Link
                                        :href="route('parent.students.reports.view', { student: student.id, term: report.academic_term.id })"
                                        class="inline-flex items-center px-3 py-2 border border-blue-600 text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </Link>
                                    <a
                                        :href="route('parent.students.reports.download', { student: student.id, term: report.academic_term.id })"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Download PDF
                                    </a>
                                </div>
                                <div v-else class="text-sm text-gray-500">
                                    Raport sedang diproses
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
