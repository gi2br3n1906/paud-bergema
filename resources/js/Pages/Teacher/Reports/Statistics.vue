<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classrooms: Array,
    academicTerms: Array,
    selectedClassroom: Object,
    selectedTerm: Object,
    statistics: Object,
});

const classroomId = ref(props.selectedClassroom?.id || '');
const termId = ref(props.selectedTerm?.id || '');

const loadStatistics = () => {
    if (classroomId.value && termId.value) {
        router.get(route('teacher.reports.statistics'), {
            classroom_id: classroomId.value,
            term_id: termId.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const getScoreColor = (score) => {
    const colors = {
        'BSB': 'bg-green-500',
        'BSH': 'bg-blue-500',
        'MB': 'bg-yellow-500',
        'BB': 'bg-red-500',
    };
    return colors[score] || 'bg-gray-500';
};

const getScoreLabel = (score) => {
    const labels = {
        'BSB': 'Berkembang Sangat Baik',
        'BSH': 'Berkembang Sesuai Harapan',
        'MB': 'Mulai Berkembang',
        'BB': 'Belum Berkembang',
    };
    return labels[score] || score;
};

const totalAssessments = computed(() => {
    if (!props.statistics) return 0;
    return Object.values(props.statistics.score_distribution).reduce((a, b) => a + b, 0);
});

const getPercentage = (value, total) => {
    return total > 0 ? Math.round((value / total) * 100) : 0;
};

const getScorePercentageBar = (score, count) => {
    const percentage = getPercentage(count, totalAssessments.value);
    return `width: ${percentage}%`;
};
</script>

<template>
    <Head title="Statistik Raport" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Statistik Raport</h2>
                    <p class="mt-1 text-sm text-gray-600">Analisis perkembangan siswa per kelas dan semester</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                            <select
                                v-model="classroomId"
                                @change="loadStatistics"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Pilih Kelas</option>
                                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                    {{ classroom.name }} - {{ classroom.academic_year?.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                            <select
                                v-model="termId"
                                @change="loadStatistics"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Pilih Semester</option>
                                <option v-for="term in academicTerms" :key="term.id" :value="term.id">
                                    {{ term.semester }} - {{ term.academic_year?.year }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                v-if="selectedClassroom && selectedTerm"
                                @click="() => { classroomId = ''; termId = ''; router.get(route('teacher.reports.statistics')); }"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No Data State -->
                <div v-if="!statistics" class="bg-white shadow-sm rounded-lg p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Belum Ada Data</h3>
                    <p class="mt-2 text-sm text-gray-500">Pilih kelas dan semester untuk melihat statistik raport</p>
                </div>

                <!-- Statistics Display -->
                <div v-else>
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Siswa</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_students }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Sudah Publish</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.completed_reports }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Draft</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.draft_reports }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Belum Dibuat</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.not_started }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Score Distribution -->
                    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Nilai</h3>
                        <div class="space-y-4">
                            <div v-for="(count, score) in statistics.score_distribution" :key="score">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-3">
                                        <div :class="getScoreColor(score)" class="h-8 w-8 rounded flex items-center justify-center">
                                            <span class="text-white text-xs font-bold">{{ score }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ getScoreLabel(score) }}</span>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ count }} ({{ getPercentage(count, totalAssessments) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div
                                        :class="getScoreColor(score)"
                                        class="h-3 rounded-full transition-all duration-500"
                                        :style="getScorePercentageBar(score, count)"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">Total Penilaian: <span class="font-semibold text-gray-900">{{ totalAssessments }}</span></p>
                        </div>
                    </div>

                    <!-- Aspect Performance -->
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performa per Aspek Perkembangan</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aspek</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">BSB</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">BSH</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">MB</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">BB</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="aspect in statistics.aspect_performance" :key="aspect.name" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ aspect.name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ aspect.category }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-gray-900">{{ aspect.scores.BSB }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-gray-900">{{ aspect.scores.BSH }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-gray-900">{{ aspect.scores.MB }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-gray-900">{{ aspect.scores.BB }}</td>
                                        <td class="px-6 py-4 text-sm text-center font-semibold text-gray-900">{{ aspect.total }}</td>
                                        <td class="px-6 py-4 text-sm text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': aspect.average_score >= 3.5,
                                                    'bg-blue-100 text-blue-800': aspect.average_score >= 2.5 && aspect.average_score < 3.5,
                                                    'bg-yellow-100 text-yellow-800': aspect.average_score >= 1.5 && aspect.average_score < 2.5,
                                                    'bg-red-100 text-red-800': aspect.average_score < 1.5,
                                                }"
                                            >
                                                {{ aspect.average_score }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Catatan:</strong> Rata-rata dihitung dengan bobot: BSB=4, BSH=3, MB=2, BB=1
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
