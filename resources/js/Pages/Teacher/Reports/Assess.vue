<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import axios from 'axios';

const props = defineProps({
    student: Object,
    academicTerm: Object,
    reportCard: Object,
    assessmentAspects: Array,
    existingDetails: Object,
});

// Initialize form with existing data or empty values
const form = useForm({
    assessments: props.assessmentAspects.map(aspect => {
        const existing = props.existingDetails?.[aspect.id];
        return {
            assessment_aspect_id: aspect.id,
            score: existing?.score || '',
            keywords: existing?.keywords || '',
            narrative: existing?.narrative || '',
        };
    }),
});

const scoreOptions = [
    { value: 'BB', label: 'BB - Belum Berkembang' },
    { value: 'MB', label: 'MB - Mulai Berkembang' },
    { value: 'BSH', label: 'BSH - Berkembang Sesuai Harapan' },
    { value: 'BSB', label: 'BSB - Berkembang Sangat Baik' },
];

const generatingNarrative = ref({});
const generatingAll = ref(false);

const generateNarrative = async (index) => {
    const assessment = form.assessments[index];
    const aspect = props.assessmentAspects[index];

    if (!assessment.score) {
        alert('Silakan pilih nilai terlebih dahulu');
        return;
    }

    generatingNarrative.value[index] = true;

    try {
        const response = await axios.post(
            route('teacher.reports.generate-narrative', {
                student: props.student.id,
                term: props.academicTerm.id,
                aspect: aspect.id,
            }),
            {
                score: assessment.score,
                keywords: assessment.keywords,
            }
        );

        if (response.data.success) {
            form.assessments[index].narrative = response.data.narrative;
        } else {
            alert(response.data.message || 'Gagal generate narasi');
        }
    } catch (error) {
        console.error('Error generating narrative:', error);
        alert('Terjadi kesalahan saat generate narasi. Pastikan API key Gemini sudah dikonfigurasi.');
    } finally {
        generatingNarrative.value[index] = false;
    }
};

const generateAllNarratives = async () => {
    // Check if all scores are filled
    const allScoresFilled = form.assessments.every(a => a.score);
    if (!allScoresFilled) {
        alert('Silakan lengkapi semua nilai terlebih dahulu');
        return;
    }

    generatingAll.value = true;

    try {
        const response = await axios.post(
            route('teacher.reports.generate-bulk-narratives', {
                student: props.student.id,
                term: props.academicTerm.id,
            }),
            {
                assessments: form.assessments,
            }
        );

        if (response.data.success) {
            // Update narratives from response
            Object.entries(response.data.narratives).forEach(([aspectId, narrative]) => {
                const index = form.assessments.findIndex(
                    a => a.assessment_aspect_id == aspectId
                );
                if (index !== -1) {
                    form.assessments[index].narrative = narrative;
                }
            });
        } else {
            alert(response.data.message || 'Gagal generate narasi');
        }
    } catch (error) {
        console.error('Error generating narratives:', error);
        alert('Terjadi kesalahan saat generate narasi. Pastikan API key Gemini sudah dikonfigurasi.');
    } finally {
        generatingAll.value = false;
    }
};

const saveDraft = () => {
    form.post(route('teacher.reports.save-assessment', {
        student: props.student.id,
        term: props.academicTerm.id,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by backend redirect with success message
        },
    });
};

const cancel = () => {
    router.visit(route('teacher.reports.students', {
        classroom: props.student.classroom_id,
        term: props.academicTerm.id,
    }));
};

const publishing = ref(false);

const publishReport = () => {
    if (!confirm('Yakin ingin publish raport ini? Setelah dipublish, raport tidak bisa diedit lagi dan akan bisa dilihat oleh orang tua.')) {
        return;
    }

    publishing.value = true;

    router.post(route('teacher.reports.publish', {
        student: props.student.id,
        term: props.academicTerm.id,
    }), {}, {
        preserveScroll: true,
        onFinish: () => {
            publishing.value = false;
        },
    });
};

const downloadPdf = () => {
    window.open(route('teacher.reports.download-pdf', {
        student: props.student.id,
        term: props.academicTerm.id,
    }), '_blank');
};
</script>

<template>
    <Head title="Input Penilaian" />

    <TeacherLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Input Penilaian Raport</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ student.name }} - {{ student.classroom?.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ academicTerm.name }} ({{ academicTerm.academic_year?.year }})
                            </p>
                        </div>
                        <div class="text-right space-y-2">
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                      :class="reportCard.status === 'draft'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-green-100 text-green-800'">
                                    {{ reportCard.status === 'draft' ? 'Draft' : 'Sudah Dipublish' }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 justify-end">
                                <button
                                    @click="downloadPdf"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Form -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Aspek Penilaian</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Pilih nilai dan masukkan kata kunci untuk setiap aspek perkembangan
                        </p>
                    </div>

                    <form @submit.prevent="saveDraft" class="divide-y divide-gray-200">
                        <!-- Assessment Aspects -->
                        <div v-for="(aspect, index) in assessmentAspects"
                             :key="aspect.id"
                             class="px-6 py-5">
                            <div class="space-y-4">
                                <!-- Aspect Header -->
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ index + 1 }}. {{ aspect.name }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ aspect.description }}</p>
                                </div>

                                <!-- Score Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nilai <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.assessments[index].score"
                                        :disabled="reportCard.status === 'published'"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        required>
                                        <option value="">-- Pilih Nilai --</option>
                                        <option v-for="option in scoreOptions"
                                                :key="option.value"
                                                :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Keywords Input -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Kata Kunci (opsional)
                                        <span class="text-gray-500 font-normal">
                                            - untuk AI generate narasi
                                        </span>
                                    </label>
                                    <textarea
                                        v-model="form.assessments[index].keywords"
                                        :disabled="reportCard.status === 'published'"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        placeholder="Contoh: mandiri, percaya diri, aktif bertanya, gemar membaca, dll. (pisahkan dengan koma)"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Masukkan kata kunci yang menggambarkan perkembangan anak untuk aspek ini.
                                    </p>
                                </div>

                                <!-- Generate Narrative Button -->
                                <div>
                                    <button
                                        type="button"
                                        @click="generateNarrative(index)"
                                        :disabled="!form.assessments[index].score || generatingNarrative[index] || reportCard.status === 'published'"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg v-if="generatingNarrative[index]" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        {{ generatingNarrative[index] ? 'Generating...' : 'Generate Narasi AI' }}
                                    </button>
                                </div>

                                <!-- Narrative Display/Edit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Narasi Deskripsi
                                        <span class="text-gray-500 font-normal text-xs">
                                            (akan muncul di raport)
                                        </span>
                                    </label>
                                    <textarea
                                        v-model="form.assessments[index].narrative"
                                        :disabled="reportCard.status === 'published'"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        :class="form.assessments[index].narrative ? 'bg-green-50' : ''"
                                        placeholder="Klik tombol 'Generate Narasi AI' untuk membuat narasi otomatis, atau tulis manual di sini"></textarea>
                                    <p v-if="form.assessments[index].narrative" class="mt-1 text-xs text-green-600 flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Narasi sudah diisi. Anda dapat mengedit manual jika diperlukan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                            <button
                                type="button"
                                @click="cancel"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Batal
                            </button>

                            <div class="flex items-center space-x-3">
                                <button
                                    type="button"
                                    @click="generateAllNarratives"
                                    :disabled="generatingAll || reportCard.status === 'published'"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="generatingAll" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ generatingAll ? 'Generating Semua...' : 'Generate Semua Narasi' }}
                                </button>

                                <button
                                    v-if="reportCard.status === 'draft'"
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>Simpan Draft</span>
                                </button>

                                <button
                                    v-if="reportCard.status === 'draft'"
                                    type="button"
                                    @click="publishReport"
                                    :disabled="publishing"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="publishing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ publishing ? 'Publishing...' : 'Publish Raport' }}
                                </button>

                                <div v-else class="text-green-600 text-sm font-medium flex items-center">
                                    <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Raport sudah dipublish (read-only)
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Help Text -->
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Panduan Penilaian</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li><strong>BB (Belum Berkembang):</strong> Anak belum menunjukkan kemampuan sesuai indikator</li>
                                    <li><strong>MB (Mulai Berkembang):</strong> Anak mulai menunjukkan kemampuan dengan bantuan</li>
                                    <li><strong>BSH (Berkembang Sesuai Harapan):</strong> Anak menunjukkan kemampuan secara mandiri</li>
                                    <li><strong>BSB (Berkembang Sangat Baik):</strong> Anak menunjukkan kemampuan melebihi harapan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
