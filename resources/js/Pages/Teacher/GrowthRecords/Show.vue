<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm, Link } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'

const props = defineProps({
    student: Object,
    records: Array
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const editingRecord = ref(null)

const addForm = useForm({
    student_id: props.student.id,
    measurement_date: new Date().toISOString().split('T')[0],
    height: '',
    weight: '',
    head_circumference: '',
    notes: ''
})

const editForm = useForm({
    measurement_date: '',
    height: '',
    weight: '',
    head_circumference: '',
    notes: ''
})

const openAddModal = () => {
    addForm.reset()
    addForm.student_id = props.student.id
    addForm.measurement_date = new Date().toISOString().split('T')[0]
    showAddModal.value = true
}

const closeAddModal = () => {
    showAddModal.value = false
    addForm.reset()
}

const submitAdd = () => {
    addForm.post(route('teacher.growth-records.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddModal()
        }
    })
}

const openEditModal = (record) => {
    editingRecord.value = record
    editForm.measurement_date = record.measurement_date
    editForm.height = record.height
    editForm.weight = record.weight
    editForm.head_circumference = record.head_circumference
    editForm.notes = record.notes
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    editingRecord.value = null
    editForm.reset()
}

const submitEdit = () => {
    editForm.put(route('teacher.growth-records.update', editingRecord.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal()
        }
    })
}

const deleteRecord = (recordId) => {
    if (confirm('Apakah Anda yakin ingin menghapus data pengukuran ini?')) {
        router.delete(route('teacher.growth-records.destroy', recordId), {
            preserveScroll: true
        })
    }
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
    let years = today.getFullYear() - birth.getFullYear()
    let months = today.getMonth() - birth.getMonth()

    if (months < 0) {
        years--
        months += 12
    }

    return `${years} tahun ${months} bulan`
}

const chartData = computed(() => {
    if (props.records.length === 0) return null

    // Prepare data for simple visualization
    const sortedRecords = [...props.records].sort((a, b) =>
        new Date(a.measurement_date) - new Date(b.measurement_date)
    )

    return {
        dates: sortedRecords.map(r => formatDate(r.measurement_date)),
        heights: sortedRecords.map(r => r.height),
        weights: sortedRecords.map(r => r.weight)
    }
})
</script>

<template>
    <Head :title="`Tumbuh Kembang - ${student.name}`" />

    <TeacherLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('teacher.growth-records.index')" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ student.name }}</h1>
                        <p class="text-sm text-gray-500">Riwayat Tumbuh Kembang</p>
                    </div>
                </div>
                <button
                    @click="openAddModal"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pengukuran
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Student Info Card -->
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">NISN</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ student.nisn }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Jenis Kelamin</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ student.gender }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tanggal Lahir</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatDate(student.date_of_birth) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Usia</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ getAge(student.date_of_birth) }}</p>
                    </div>
                </div>
            </div>

            <!-- Records List -->
            <div class="rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Riwayat Pengukuran</h3>
                </div>

                <div v-if="records.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">Belum ada data pengukuran</h3>
                    <p class="mt-2 text-sm text-gray-500">Klik tombol "Tambah Pengukuran" untuk memulai mencatat data tumbuh kembang.</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="record in records"
                        :key="record.id"
                        class="px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ formatDate(record.measurement_date) }}</p>
                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Tinggi Badan</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ record.height }} cm</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Berat Badan</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ record.weight }} kg</p>
                                    </div>
                                    <div v-if="record.head_circumference">
                                        <p class="text-xs text-gray-500">Lingkar Kepala</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ record.head_circumference }} cm</p>
                                    </div>
                                </div>
                                <p v-if="record.notes" class="mt-2 text-sm text-gray-600">{{ record.notes }}</p>
                            </div>

                            <div class="flex items-center space-x-2 ml-4">
                                <button
                                    @click="openEditModal(record)"
                                    class="rounded-lg p-2 text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Edit"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteRecord(record.id)"
                                    class="rounded-lg p-2 text-red-600 hover:bg-red-50 transition-colors"
                                    title="Hapus"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeAddModal"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitAdd">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Tambah Pengukuran Baru</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Pengukuran</label>
                                    <input
                                        v-model="addForm.measurement_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tinggi (cm)</label>
                                        <input
                                            v-model="addForm.height"
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            max="300"
                                            required
                                            placeholder="85.5"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Berat (kg)</label>
                                        <input
                                            v-model="addForm.weight"
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            max="200"
                                            required
                                            placeholder="12.5"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lingkar Kepala (cm) <span class="text-gray-400">(Opsional)</span></label>
                                    <input
                                        v-model="addForm.head_circumference"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        max="100"
                                        placeholder="48.5"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                    <textarea
                                        v-model="addForm.notes"
                                        rows="3"
                                        placeholder="Catatan tambahan..."
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button
                                type="submit"
                                :disabled="addForm.processing"
                                class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 sm:ml-3 sm:w-auto"
                            >
                                {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button
                                type="button"
                                @click="closeAddModal"
                                class="mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:mt-0 sm:w-auto"
                            >
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeEditModal"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form @submit.prevent="submitEdit">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Edit Pengukuran</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Pengukuran</label>
                                    <input
                                        v-model="editForm.measurement_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tinggi (cm)</label>
                                        <input
                                            v-model="editForm.height"
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            max="300"
                                            required
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Berat (kg)</label>
                                        <input
                                            v-model="editForm.weight"
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            max="200"
                                            required
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lingkar Kepala (cm) <span class="text-gray-400">(Opsional)</span></label>
                                    <input
                                        v-model="editForm.head_circumference"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        max="100"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Catatan <span class="text-gray-400">(Opsional)</span></label>
                                    <textarea
                                        v-model="editForm.notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 sm:ml-3 sm:w-auto"
                            >
                                {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:mt-0 sm:w-auto"
                            >
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </TeacherLayout>
</template>