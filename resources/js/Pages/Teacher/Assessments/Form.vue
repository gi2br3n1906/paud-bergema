<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    classroom: Object,
    academicTerm: Object,
    students: Array,
    assessmentAspects: Array,
    existingAssessments: Object
})

// Group aspects by category
const groupedAspects = computed(() => {
    const groups = {}
    props.assessmentAspects.forEach(aspect => {
        if (!groups[aspect.category]) {
            groups[aspect.category] = []
        }
        groups[aspect.category].push(aspect)
    })
    return groups
})

// Initialize assessments data
const assessments = reactive({})

// Load existing assessments
props.students.forEach(student => {
    assessments[student.id] = {}
    props.assessmentAspects.forEach(aspect => {
        const key = `${student.id}_${aspect.id}`
        const existing = props.existingAssessments[key]
        assessments[student.id][aspect.id] = {
            score: existing?.score || null,
            notes: existing?.notes || ''
        }
    })
})

const saving = ref(false)
const saveMessage = ref('')

const saveAssessments = async () => {
    const assessmentsArray = []

    Object.keys(assessments).forEach(studentId => {
        Object.keys(assessments[studentId]).forEach(aspectId => {
            const assessment = assessments[studentId][aspectId]
            if (assessment.score) {
                assessmentsArray.push({
                    student_id: parseInt(studentId),
                    assessment_aspect_id: parseInt(aspectId),
                    score: assessment.score,
                    notes: assessment.notes
                })
            }
        })
    })

    if (assessmentsArray.length === 0) {
        alert('Tidak ada penilaian yang diisi')
        return
    }

    saving.value = true
    saveMessage.value = 'Menyimpan...'

    router.post(route('teacher.assessments.store'), {
        classroom_id: props.classroom.id,
        academic_term_id: props.academicTerm.id,
        assessments: assessmentsArray
    }, {
        preserveScroll: true,
        onSuccess: () => {
            saveMessage.value = 'Penilaian berhasil disimpan!'
            setTimeout(() => {
                saveMessage.value = ''
            }, 3000)
        },
        onError: () => {
            saveMessage.value = 'Gagal menyimpan penilaian'
            setTimeout(() => {
                saveMessage.value = ''
            }, 3000)
        },
        onFinish: () => {
            saving.value = false
        }
    })
}

const getScoreColor = (score) => {
    const colors = {
        'BB': 'bg-red-100 text-red-800 border-red-300',
        'MB': 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'BSH': 'bg-blue-100 text-blue-800 border-blue-300',
        'BSB': 'bg-green-100 text-green-800 border-green-300'
    }
    return colors[score] || 'bg-gray-100 text-gray-800 border-gray-300'
}
</script>

<template>
    <Head :title="`Penilaian - ${classroom.name}`" />

    <TeacherLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ classroom.name }}</h1>
                    <p class="text-sm text-gray-500">{{ academicTerm.name }} - {{ academicTerm.academic_year.name }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span v-if="saveMessage" class="text-sm font-medium" :class="saveMessage.includes('berhasil') ? 'text-green-600' : 'text-red-600'">
                        {{ saveMessage }}
                    </span>
                    <button
                        @click="saveAssessments"
                        :disabled="saving"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition-colors"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        {{ saving ? 'Menyimpan...' : 'Simpan Penilaian' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Legend -->
            <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Skala Penilaian:</h3>
                <div class="flex flex-wrap gap-3">
                    <div class="flex items-center">
                        <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium bg-red-100 text-red-800 border border-red-300">BB</span>
                        <span class="ml-2 text-xs text-gray-600">Belum Berkembang</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-300">MB</span>
                        <span class="ml-2 text-xs text-gray-600">Mulai Berkembang</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 border border-blue-300">BSH</span>
                        <span class="ml-2 text-xs text-gray-600">Berkembang Sesuai Harapan</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium bg-green-100 text-green-800 border border-green-300">BSB</span>
                        <span class="ml-2 text-xs text-gray-600">Berkembang Sangat Baik</span>
                    </div>
                </div>
            </div>

            <!-- Assessment Forms by Category -->
            <div v-for="(aspects, category) in groupedAspects" :key="category" class="rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">{{ category }}</h3>
                </div>

                <div class="p-6">
                    <div v-for="aspect in aspects" :key="aspect.id" class="mb-8 last:mb-0">
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">{{ aspect.name }}</h4>
                            <p v-if="aspect.description" class="mt-1 text-xs text-gray-500">{{ aspect.description }}</p>
                        </div>

                        <div class="space-y-3">
                            <div v-for="student in students" :key="student.id" class="flex items-start space-x-4 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ student.name }}</p>
                                    <p class="text-xs text-gray-500">NISN: {{ student.nisn }}</p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <button
                                            v-for="score in ['BB', 'MB', 'BSH', 'BSB']"
                                            :key="score"
                                            @click="assessments[student.id][aspect.id].score = score"
                                            type="button"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all"
                                            :class="assessments[student.id][aspect.id].score === score
                                                ? getScoreColor(score)
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'"
                                        >
                                            {{ score }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Save Button (Mobile) -->
            <div class="sticky bottom-4 md:hidden">
                <button
                    @click="saveAssessments"
                    :disabled="saving"
                    class="w-full inline-flex justify-center items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    {{ saving ? 'Menyimpan...' : 'Simpan Penilaian' }}
                </button>
            </div>
        </div>
    </TeacherLayout>
</template>
