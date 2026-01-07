<script setup>
import { ref } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    classrooms: Array,
    selectedClassroom: Object,
    students: Array
})

const selectedClassroomId = ref(props.selectedClassroom?.id || null)

const handleClassroomChange = () => {
    router.get(route('teacher.growth-records.index'), {
        classroom_id: selectedClassroomId.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

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
    let age = today.getFullYear() - birth.getFullYear()
    const monthDiff = today.getMonth() - birth.getMonth()
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--
    }
    return `${age} tahun`
}
</script>

<template>
    <Head title="Catatan Tumbuh Kembang" />

    <TeacherLayout>
        <template #header>
            Catatan Tumbuh Kembang
        </template>

        <div class="space-y-6">
            <!-- Filter -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Kelas
                    </label>
                    <select
                        v-model="selectedClassroomId"
                        @change="handleClassroomChange"
                        class="block w-full max-w-md rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                    {{ !selectedClassroom ? 'Pilih kelas dari dropdown di atas untuk melihat data tumbuh kembang siswa.' : 'Kelas ini belum memiliki siswa yang terdaftar.' }}
                </p>
            </div>

            <!-- Students List -->
            <div v-else class="rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Siswa</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Klik siswa untuk melihat riwayat lengkap dan menambah data pengukuran
                    </p>
                </div>

                <div class="divide-y divide-gray-200">
                    <Link
                        v-for="student in students"
                        :key="student.id"
                        :href="route('teacher.growth-records.show', student.id)"
                        class="block px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ student.name }}</h4>
                                        <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                                            <span>NISN: {{ student.nisn }}</span>
                                            <span>•</span>
                                            <span>{{ student.gender }}</span>
                                            <span>•</span>
                                            <span>{{ getAge(student.date_of_birth) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-6">
                                <div v-if="student.latest_record" class="text-right">
                                    <p class="text-xs font-medium text-gray-500">Pengukuran Terakhir</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(student.latest_record.measurement_date) }}</p>
                                    <div class="mt-2 flex items-center space-x-3 text-xs text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                            </svg>
                                            {{ student.latest_record.height }} cm
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                            </svg>
                                            {{ student.latest_record.weight }} kg
                                        </span>
                                        <span v-if="student.latest_record.head_circumference" class="flex items-center">
                                            <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                            </svg>
                                            {{ student.latest_record.head_circumference }} cm
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="text-right">
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                        Belum ada data
                                    </span>
                                </div>

                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
