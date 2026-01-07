<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    classrooms: Array,
    selectedClassroom: Object,
    students: Array,
    selectedDate: String,
    existingLogs: Object
})

const selectedClassroomId = ref(props.selectedClassroom?.id || null)
const selectedDate = ref(props.selectedDate)

// Initialize form data for each student
const studentLogs = ref({})
props.students.forEach(student => {
    const existingLog = props.existingLogs[student.id]
    studentLogs.value[student.id] = {
        student_id: student.id,
        attendance_status: existingLog?.attendance_status || 'Hadir',
        prayer_quality: existingLog?.prayer_quality || '',
        quran_surah: existingLog?.quran_surah || '',
        quran_verses: existingLog?.quran_verses || '',
        notes: existingLog?.notes || ''
    }
})

const attendanceOptions = [
    { value: 'Hadir', label: 'Hadir', color: 'green' },
    { value: 'Sakit', label: 'Sakit', color: 'yellow' },
    { value: 'Izin', label: 'Izin', color: 'blue' },
    { value: 'Alpa', label: 'Alpa', color: 'red' }
]

const prayerOptions = [
    { value: 'Baik', label: 'Baik' },
    { value: 'Cukup', label: 'Cukup' },
    { value: 'Perlu Bimbingan', label: 'Perlu Bimbingan' }
]

const handleClassroomChange = () => {
    router.get(route('teacher.daily-logs.index'), {
        classroom_id: selectedClassroomId.value,
        date: selectedDate.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const handleDateChange = () => {
    router.get(route('teacher.daily-logs.index'), {
        classroom_id: selectedClassroomId.value,
        date: selectedDate.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const saveForm = useForm({
    classroom_id: props.selectedClassroom?.id,
    log_date: props.selectedDate,
    logs: []
})

const saveLogs = () => {
    // Convert studentLogs object to array
    saveForm.logs = Object.values(studentLogs.value)
    saveForm.classroom_id = selectedClassroomId.value
    saveForm.log_date = selectedDate.value

    saveForm.post(route('teacher.daily-logs.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be reset automatically
        }
    })
}

const attendanceColor = (status) => {
    const option = attendanceOptions.find(opt => opt.value === status)
    return option?.color || 'gray'
}

const attendanceSummary = computed(() => {
    const summary = {
        Hadir: 0,
        Sakit: 0,
        Izin: 0,
        Alpa: 0
    }

    Object.values(studentLogs.value).forEach(log => {
        if (summary[log.attendance_status] !== undefined) {
            summary[log.attendance_status]++
        }
    })

    return summary
})
</script>

<template>
    <Head title="Log Harian Siswa" />

    <TeacherLayout>
        <template #header>
            Log Harian Siswa
        </template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Kelas
                        </label>
                        <select
                            v-model="selectedClassroomId"
                            @change="handleClassroomChange"
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
                            Tanggal
                        </label>
                        <input
                            v-model="selectedDate"
                            @change="handleDateChange"
                            type="date"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div v-if="students.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Hadir</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ attendanceSummary.Hadir }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100">
                                <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Sakit</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ attendanceSummary.Sakit }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Izin</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ attendanceSummary.Izin }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Alpa</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ attendanceSummary.Alpa }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!selectedClassroom || students.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm border border-gray-200">
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
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                    />
                </svg>
                <h3 class="mt-4 text-sm font-medium text-gray-900">
                    {{ !selectedClassroom ? 'Pilih kelas terlebih dahulu' : 'Tidak ada siswa di kelas ini' }}
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    {{ !selectedClassroom ? 'Pilih kelas dari dropdown di atas untuk mulai mencatat log harian.' : 'Kelas ini belum memiliki siswa yang terdaftar.' }}
                </p>
            </div>

            <!-- Daily Log Form -->
            <form v-else @submit.prevent="saveLogs" class="space-y-4">
                <div
                    v-for="student in students"
                    :key="student.id"
                    class="rounded-lg bg-white p-6 shadow-sm border border-gray-200"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ student.name }}</h3>
                            <p class="text-sm text-gray-500">NISN: {{ student.nisn }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Attendance Status -->
                        <div class="lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status Kehadiran
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label
                                    v-for="option in attendanceOptions"
                                    :key="option.value"
                                    :class="[
                                        'relative flex items-center justify-center rounded-lg border-2 px-4 py-3 cursor-pointer transition-all',
                                        studentLogs[student.id].attendance_status === option.value
                                            ? `border-${option.color}-500 bg-${option.color}-50`
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="studentLogs[student.id].attendance_status"
                                        :value="option.value"
                                        class="sr-only"
                                    />
                                    <span
                                        :class="[
                                            'text-sm font-medium',
                                            studentLogs[student.id].attendance_status === option.value
                                                ? `text-${option.color}-700`
                                                : 'text-gray-700'
                                        ]"
                                    >
                                        {{ option.label }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Prayer Quality -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kualitas Ibadah
                            </label>
                            <select
                                v-model="studentLogs[student.id].prayer_quality"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Pilih...</option>
                                <option
                                    v-for="option in prayerOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Quran Surah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Surah Al-Quran
                            </label>
                            <input
                                v-model="studentLogs[student.id].quran_surah"
                                type="text"
                                placeholder="Contoh: Al-Fatihah"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Quran Verses -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ayat
                            </label>
                            <input
                                v-model="studentLogs[student.id].quran_verses"
                                type="text"
                                placeholder="Contoh: 1-7"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Notes -->
                        <div class="lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Tambahan
                            </label>
                            <textarea
                                v-model="studentLogs[student.id].notes"
                                rows="2"
                                placeholder="Catatan khusus untuk hari ini..."
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-3 rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                    <button
                        type="submit"
                        :disabled="saveForm.processing"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg
                            v-if="saveForm.processing"
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ saveForm.processing ? 'Menyimpan...' : 'Simpan Log Harian' }}
                    </button>
                </div>
            </form>
        </div>
    </TeacherLayout>
</template>
