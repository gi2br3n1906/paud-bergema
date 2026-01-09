<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    student: Object,
    academicTerm: Object,
    reportCard: Object,
    reportDetails: Array,
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

const getScoreBadgeColor = (score) => {
    const colors = {
        'BSB': 'bg-green-500',
        'BSH': 'bg-blue-500',
        'MB': 'bg-yellow-500',
        'BB': 'bg-red-500',
    };
    return colors[score] || 'bg-gray-500';
};

const getScoreDescription = (score) => {
    const descriptions = {
        'BSB': 'Berkembang Sangat Baik',
        'BSH': 'Berkembang Sesuai Harapan',
        'MB': 'Mulai Berkembang',
        'BB': 'Belum Berkembang',
    };
    return descriptions[score] || '-';
};
</script>

<template>
    <Head :title="`Raport ${student.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <Link :href="route('parent.students.profile', student.id)" class="mr-4">
                            <svg class="h-6 w-6 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <div class="bg-blue-600 text-white rounded-full p-2 mr-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Raport Anak</h1>
                            <p class="text-sm text-gray-500">{{ student.name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a
                            :href="route('parent.students.reports.download', { student: student.id, term: academicTerm.id })"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                        </a>
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
                <!-- Report Header Card -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Laporan Perkembangan Anak Didik</h2>
                            <p class="text-blue-100 mt-2">{{ student.name }}</p>
                            <p class="text-blue-100">{{ academicTerm.semester }} - Tahun Ajaran {{ academicTerm.academic_year.year }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-blue-100">Dipublish pada</p>
                            <p class="text-lg font-semibold">{{ formatDate(reportCard.published_at) }}</p>
                            <p class="text-sm text-blue-100 mt-1">oleh {{ reportCard.creator.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Legend Card -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Keterangan Capaian Perkembangan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-green-500 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">BSB</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Berkembang Sangat Baik</p>
                                <p class="text-xs text-gray-500">Melebihi harapan</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-blue-500 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">BSH</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Berkembang Sesuai Harapan</p>
                                <p class="text-xs text-gray-500">Sudah mandiri</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-yellow-500 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">MB</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Mulai Berkembang</p>
                                <p class="text-xs text-gray-500">Dengan bantuan</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-red-500 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">BB</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Belum Berkembang</p>
                                <p class="text-xs text-gray-500">Perlu stimulus</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Penilaian Aspek Perkembangan</h3>

                    <div class="space-y-4">
                        <div
                            v-for="(detail, index) in reportDetails"
                            :key="detail.id"
                            class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-600 font-semibold text-sm">
                                            {{ index + 1 }}
                                        </span>
                                        <h4 class="text-base font-semibold text-gray-900">{{ detail.assessment_aspect.name }}</h4>
                                    </div>
                                    <p v-if="detail.assessment_aspect.description" class="text-sm text-gray-600 ml-11">
                                        {{ detail.assessment_aspect.description }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <div
                                        class="h-12 w-12 rounded-lg flex items-center justify-center"
                                        :class="getScoreBadgeColor(detail.score)"
                                    >
                                        <span class="text-white font-bold">{{ detail.score }}</span>
                                    </div>
                                    <p class="text-xs text-center text-gray-500 mt-1">{{ getScoreDescription(detail.score) }}</p>
                                </div>
                            </div>

                            <div v-if="detail.narrative" class="ml-11 mt-3 bg-blue-50 border border-blue-100 rounded-lg p-4">
                                <p class="text-sm font-medium text-blue-900 mb-1">Catatan Perkembangan:</p>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ detail.narrative }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Catatan</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Raport ini menggambarkan perkembangan anak selama satu semester</li>
                                    <li>Klik "Download PDF" untuk mendapatkan salinan raport dalam format PDF</li>
                                    <li>Untuk konsultasi lebih lanjut, silakan hubungi guru kelas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
