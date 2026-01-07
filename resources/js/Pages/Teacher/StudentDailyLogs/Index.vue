<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    logs: Object,
    classrooms: Array,
    filters: Object,
})

const filterForm = useForm({
    classroom_id: props.filters.classroom_id || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
})

const applyFilters = () => {
    router.get(route('teacher.daily-logs.index'), filterForm.data(), {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    filterForm.reset()
    router.get(route('teacher.daily-logs.index'))
}

// Modal state
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedLog = ref(null)

const createForm = useForm({
    student_id: '',
    date: new Date().toISOString().split('T')[0],
    attendance_status: 'Hadir',
    arrival_time: '',
    pickup_time: '',
    mood: '',
    activities: '',
    meals: '',
    nap_notes: '',
    health_notes: '',
    notes: '',
})

const editForm = useForm({
    date: '',
    attendance_status: '',
    arrival_time: '',
    pickup_time: '',
    mood: '',
    activities: '',
    meals: '',
    nap_notes: '',
    health_notes: '',
    notes: '',
})

const deleteForm = useForm({})

// Students for selected classroom
const students = ref([])
const selectedClassroom = ref('')

const loadStudents = async () => {
    if (!selectedClassroom.value) {
        students.value = []
        return
    }

    try {
        const response = await fetch(
            route('teacher.daily-logs.students-by-classroom') +
                `?classroom_id=${selectedClassroom.value}&date=${createForm.date}`
        )
        const data = await response.json()
        students.value = data.students
    } catch (error) {
        console.error('Error loading students:', error)
    }
}

const openCreateModal = () => {
    createForm.reset()
    createForm.date = new Date().toISOString().split('T')[0]
    createForm.attendance_status = 'Hadir'
    showCreateModal.value = true
}

const submitCreate = () => {
    createForm.post(route('teacher.daily-logs.store'), {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

const openEditModal = (log) => {
    selectedLog.value = log
    editForm.date = log.date
    editForm.attendance_status = log.attendance_status
    editForm.arrival_time = log.arrival_time || ''
    editForm.pickup_time = log.pickup_time || ''
    editForm.mood = log.mood || ''
    editForm.activities = log.activities || ''
    editForm.meals = log.meals || ''
    editForm.nap_notes = log.nap_notes || ''
    editForm.health_notes = log.health_notes || ''
    editForm.notes = log.notes || ''
    showEditModal.value = true
}

const submitEdit = () => {
    editForm.put(route('teacher.daily-logs.update', selectedLog.value.id), {
        onSuccess: () => {
            showEditModal.value = false
        },
    })
}

const openDeleteModal = (log) => {
    selectedLog.value = log
    showDeleteModal.value = true
}

const submitDelete = () => {
    deleteForm.delete(route('teacher.daily-logs.destroy', selectedLog.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
        },
    })
}

const getAttendanceColor = (status) => {
    const colors = {
        Hadir: 'bg-green-100 text-green-800',
        Sakit: 'bg-yellow-100 text-yellow-800',
        Izin: 'bg-blue-100 text-blue-800',
        Alpa: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const getMoodEmoji = (mood) => {
    const emojis = {
        Senang: '😊',
        Biasa: '😐',
        Sedih: '😢',
        Rewel: '😠',
    }
    return emojis[mood] || ''
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const options = { year: 'numeric', month: 'long', day: 'numeric' }
    return date.toLocaleDateString('id-ID', options)
}
</script>

<template>
    <Head title="Log Harian Siswa" />

    <TeacherLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Log Harian Siswa</h2>
                <button
                    @click="openCreateModal"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    + Tambah Log
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Filter</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                <select
                                    v-model="filterForm.classroom_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Semua Kelas</option>
                                    <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                        {{ classroom.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                                <input
                                    type="date"
                                    v-model="filterForm.start_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                                <input
                                    type="date"
                                    v-model="filterForm.end_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="flex items-end gap-2">
                                <button
                                    @click="applyFilters"
                                    class="flex-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Terapkan
                                </button>
                                <button
                                    @click="resetFilters"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs List -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="logs.data.length === 0" class="py-12 text-center">
                            <p class="text-gray-500">Belum ada log harian yang tercatat</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="log in logs.data"
                                :key="log.id"
                                class="rounded-lg border border-gray-200 p-4 hover:border-indigo-300"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ log.student.name }}
                                            </h3>
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                :class="getAttendanceColor(log.attendance_status)"
                                            >
                                                {{ log.attendance_status }}
                                            </span>
                                            <span v-if="log.mood" class="text-xl">
                                                {{ getMoodEmoji(log.mood) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-600">
                                            <span>{{ formatDate(log.date) }}</span>
                                            <span v-if="log.student.classroom">{{ log.student.classroom.name }}</span>
                                            <span v-if="log.arrival_time">Datang: {{ log.arrival_time }}</span>
                                            <span v-if="log.pickup_time">Pulang: {{ log.pickup_time }}</span>
                                        </div>
                                        <div v-if="log.activities" class="mt-2 text-sm text-gray-700">
                                            <strong>Kegiatan:</strong> {{ log.activities.substring(0, 100) }}{{ log.activities.length > 100 ? '...' : '' }}
                                        </div>
                                        <div v-if="log.notes" class="mt-1 text-sm text-gray-600">
                                            <strong>Catatan:</strong> {{ log.notes.substring(0, 100) }}{{ log.notes.length > 100 ? '...' : '' }}
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Dicatat oleh: {{ log.recorded_by.name }}
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="openEditModal(log)"
                                            class="rounded-md bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="openDeleteModal(log)"
                                            class="rounded-md bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-600"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="logs.links.length > 3" class="mt-6 flex justify-center gap-2">
                            <Link
                                v-for="(link, index) in logs.links"
                                :key="index"
                                :href="link.url"
                                :class="[
                                    'rounded-md px-3 py-2 text-sm',
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-50',
                                    !link.url ? 'cursor-not-allowed opacity-50' : '',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal - Simplified version, full form akan dibuat terpisah -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showCreateModal = false"></div>

                <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-medium">Tambah Log Harian</h3>
                    <p class="text-gray-600">Pilih kelas dulu, lalu pilih siswa.</p>

                    <form @submit.prevent="submitCreate" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas *</label>
                            <select
                                v-model="selectedClassroom"
                                @change="loadStudents"
                                class="mt-1 block w-full rounded-md border-gray-300"
                                required
                            >
                                <option value="">Pilih Kelas</option>
                                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                    {{ classroom.name }}
                                </option>
                            </select>
                        </div>

                        <div v-if="students.length > 0">
                            <label class="block text-sm font-medium text-gray-700">Siswa *</label>
                            <select v-model="createForm.student_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                                <option value="">Pilih Siswa</option>
                                <option v-for="student in students" :key="student.id" :value="student.id">
                                    {{ student.name }}
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal *</label>
                                <input type="date" v-model="createForm.date" class="mt-1 block w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kehadiran *</label>
                                <select v-model="createForm.attendance_status" class="mt-1 block w-full rounded-md border-gray-300" required>
                                    <option value="Hadir">Hadir</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Alpa">Alpa</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jam Datang</label>
                                <input type="time" v-model="createForm.arrival_time" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                                <input type="time" v-model="createForm.pickup_time" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mood</label>
                                <select v-model="createForm.mood" class="mt-1 block w-full rounded-md border-gray-300">
                                    <option value="">-</option>
                                    <option value="Senang">😊 Senang</option>
                                    <option value="Biasa">😐 Biasa</option>
                                    <option value="Sedih">😢 Sedih</option>
                                    <option value="Rewel">😠 Rewel</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kegiatan</label>
                            <textarea v-model="createForm.activities" rows="2" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea v-model="createForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" @click="showCreateModal = false" class="rounded-md border px-4 py-2">Batal</button>
                            <button type="submit" :disabled="createForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-white">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal (similar structure) -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showEditModal = false"></div>

                <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-medium">Edit Log: {{ selectedLog?.student.name }}</h3>

                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal *</label>
                                <input type="date" v-model="editForm.date" class="mt-1 block w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kehadiran *</label>
                                <select v-model="editForm.attendance_status" class="mt-1 block w-full rounded-md border-gray-300" required>
                                    <option value="Hadir">Hadir</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Alpa">Alpa</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jam Datang</label>
                                <input type="time" v-model="editForm.arrival_time" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                                <input type="time" v-model="editForm.pickup_time" class="mt-1 block w-full rounded-md border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mood</label>
                                <select v-model="editForm.mood" class="mt-1 block w-full rounded-md border-gray-300">
                                    <option value="">-</option>
                                    <option value="Senang">😊 Senang</option>
                                    <option value="Biasa">😐 Biasa</option>
                                    <option value="Sedih">😢 Sedih</option>
                                    <option value="Rewel">😠 Rewel</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kegiatan</label>
                            <textarea v-model="editForm.activities" rows="2" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea v-model="editForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" @click="showEditModal = false" class="rounded-md border px-4 py-2">Batal</button>
                            <button type="submit" :disabled="editForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-white">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>

                <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-medium text-red-600">Hapus Log Harian</h3>
                    <p class="text-gray-600">
                        Yakin hapus log untuk <strong>{{ selectedLog?.student.name }}</strong> tanggal <strong>{{ selectedLog?.date }}</strong>?
                    </p>

                    <div class="mt-6 flex justify-end gap-2">
                        <button @click="showDeleteModal = false" class="rounded-md border px-4 py-2">Batal</button>
                        <button @click="submitDelete" :disabled="deleteForm.processing" class="rounded-md bg-red-600 px-4 py-2 text-white">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>
