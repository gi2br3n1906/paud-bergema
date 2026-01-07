<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import TeacherLayout from '@/Layouts/TeacherLayout.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    journals: Object,
    classrooms: Array,
    filters: Object,
})

const showModal = ref(false)
const isEditing = ref(false)
const journalToDelete = ref(null)
const showDeleteModal = ref(false)
const photoPreview = ref([])

const form = useForm({
    id: null,
    classroom_id: props.filters.classroom_id || null,
    date: new Date().toISOString().split('T')[0],
    theme: '',
    activity_summary: '',
    photos: [],
    existing_photos: [],
    attendance_stats: {
        present: 0,
        sick: 0,
        permission: 0,
        absent: 0,
    },
    notes: '',
})

const searchForm = useForm({
    classroom_id: props.filters.classroom_id || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
})

const openCreateModal = () => {
    isEditing.value = false
    form.reset()
    form.date = new Date().toISOString().split('T')[0]
    form.clearErrors()
    photoPreview.value = []
    showModal.value = true
}

const openEditModal = (journal) => {
    isEditing.value = true
    form.id = journal.id
    form.classroom_id = journal.classroom_id
    form.date = journal.date
    form.theme = journal.theme || ''
    form.activity_summary = journal.activity_summary
    form.existing_photos = journal.photos || []
    form.photos = []
    form.attendance_stats = journal.attendance_stats || {
        present: 0,
        sick: 0,
        permission: 0,
        absent: 0,
    }
    form.notes = journal.notes || ''
    form.clearErrors()
    photoPreview.value = []
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.reset()
    photoPreview.value = []
}

const handlePhotoChange = (event) => {
    const files = Array.from(event.target.files)
    const totalPhotos = (form.existing_photos?.length || 0) + files.length

    if (totalPhotos > 5) {
        alert('Maksimal 5 foto per jurnal')
        event.target.value = ''
        return
    }

    form.photos = files

    // Create preview URLs
    photoPreview.value = files.map(file => URL.createObjectURL(file))
}

const removeExistingPhoto = (index) => {
    form.existing_photos.splice(index, 1)
}

const submitForm = () => {
    if (isEditing.value) {
        form.post(route('teacher.class-journals.update', form.id), {
            forceFormData: true,
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('teacher.class-journals.store'), {
            forceFormData: true,
            onSuccess: () => closeModal()
        })
    }
}

const confirmDelete = (journal) => {
    journalToDelete.value = journal
    showDeleteModal.value = true
}

const deleteJournal = () => {
    router.delete(route('teacher.class-journals.destroy', journalToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            journalToDelete.value = null
        }
    })
}

const search = () => {
    searchForm.get(route('teacher.class-journals.index'), {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    searchForm.reset()
    search()
}

const totalAttendance = computed(() => {
    const stats = form.attendance_stats
    return (stats.present || 0) + (stats.sick || 0) + (stats.permission || 0) + (stats.absent || 0)
})

const attendancePercentage = computed(() => {
    const total = totalAttendance.value
    if (total === 0) return 0
    return Math.round((form.attendance_stats.present / total) * 100)
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>
    <Head title="Berita Acara Kelas" />

    <TeacherLayout>
        <template #header>
            Berita Acara Kelas Harian
        </template>

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Jurnal Kegiatan Kelas</h2>
                    <p class="mt-1 text-sm text-gray-600">Catat aktivitas harian kelas dengan foto dokumentasi</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Berita Acara
                </PrimaryButton>
            </div>

            <!-- Filters -->
            <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <InputLabel for="filter_classroom" value="Kelas" />
                        <select
                            id="filter_classroom"
                            v-model="searchForm.classroom_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                {{ classroom.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="start_date" value="Dari Tanggal" />
                        <TextInput
                            id="start_date"
                            v-model="searchForm.start_date"
                            type="date"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel for="end_date" value="Sampai Tanggal" />
                        <TextInput
                            id="end_date"
                            v-model="searchForm.end_date"
                            type="date"
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div class="flex items-end space-x-2">
                        <PrimaryButton @click="search" class="flex-1">Filter</PrimaryButton>
                        <SecondaryButton @click="clearFilters">Reset</SecondaryButton>
                    </div>
                </div>
            </div>

            <!-- Journals List -->
            <div class="space-y-4">
                <div v-if="journals.data.length === 0" class="rounded-lg bg-white p-8 text-center shadow-sm border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Belum ada berita acara kelas</p>
                </div>

                <div
                    v-for="journal in journals.data"
                    :key="journal.id"
                    class="rounded-lg bg-white shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                                        {{ journal.classroom.name }}
                                    </span>
                                    <span v-if="journal.theme" class="text-sm text-gray-500">• {{ journal.theme }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ formatDate(journal.date) }}</p>
                                <p class="mt-1 text-xs text-gray-500">Oleh: {{ journal.teacher.name }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="openEditModal(journal)"
                                    class="rounded p-2 text-blue-600 hover:bg-blue-50"
                                    title="Edit"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="confirmDelete(journal)"
                                    class="rounded p-2 text-red-600 hover:bg-red-50"
                                    title="Hapus"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Activity Summary -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Ringkasan Aktivitas:</h4>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ journal.activity_summary }}</p>
                        </div>

                        <!-- Photos -->
                        <div v-if="journal.photos && journal.photos.length > 0" class="mb-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Dokumentasi Foto:</h4>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                                <div
                                    v-for="(photo, index) in journal.photos"
                                    :key="index"
                                    class="aspect-square rounded-lg overflow-hidden bg-gray-100"
                                >
                                    <img
                                        :src="`/storage/${photo}`"
                                        :alt="`Foto ${index + 1}`"
                                        class="h-full w-full object-cover hover:scale-105 transition-transform cursor-pointer"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Stats -->
                        <div v-if="journal.attendance_stats" class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Statistik Kehadiran:</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="rounded-lg bg-green-50 p-3">
                                    <p class="text-xs text-green-700 font-medium">Hadir</p>
                                    <p class="text-2xl font-bold text-green-800">{{ journal.attendance_stats.present || 0 }}</p>
                                </div>
                                <div class="rounded-lg bg-yellow-50 p-3">
                                    <p class="text-xs text-yellow-700 font-medium">Sakit</p>
                                    <p class="text-2xl font-bold text-yellow-800">{{ journal.attendance_stats.sick || 0 }}</p>
                                </div>
                                <div class="rounded-lg bg-blue-50 p-3">
                                    <p class="text-xs text-blue-700 font-medium">Izin</p>
                                    <p class="text-2xl font-bold text-blue-800">{{ journal.attendance_stats.permission || 0 }}</p>
                                </div>
                                <div class="rounded-lg bg-red-50 p-3">
                                    <p class="text-xs text-red-700 font-medium">Alpa</p>
                                    <p class="text-2xl font-bold text-red-800">{{ journal.attendance_stats.absent || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="journal.notes" class="mt-4 rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-600 font-medium mb-1">Catatan:</p>
                            <p class="text-sm text-gray-700">{{ journal.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="journals.links.length > 3" class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ journals.from }} to {{ journals.to }} of {{ journals.total }} results
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, index) in journals.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm',
                                link.active
                                    ? 'bg-blue-600 text-white rounded'
                                    : 'text-gray-700 hover:bg-gray-100 rounded'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="3xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Edit Berita Acara' : 'Buat Berita Acara Baru' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="classroom_id" value="Kelas *" />
                            <select
                                id="classroom_id"
                                v-model="form.classroom_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option :value="null">Pilih Kelas</option>
                                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                    {{ classroom.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.classroom_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="date" value="Tanggal *" />
                            <TextInput
                                id="date"
                                v-model="form.date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.date" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="theme" value="Tema Harian" />
                        <TextInput
                            id="theme"
                            v-model="form.theme"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Contoh: Tema Alam Semesta"
                        />
                        <InputError :message="form.errors.theme" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="activity_summary" value="Ringkasan Aktivitas *" />
                        <textarea
                            id="activity_summary"
                            v-model="form.activity_summary"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            rows="4"
                            required
                            placeholder="Deskripsikan kegiatan yang dilakukan hari ini..."
                        ></textarea>
                        <InputError :message="form.errors.activity_summary" class="mt-2" />
                    </div>

                    <!-- Existing Photos -->
                    <div v-if="isEditing && form.existing_photos.length > 0">
                        <InputLabel value="Foto Saat Ini" />
                        <div class="mt-2 grid grid-cols-5 gap-2">
                            <div
                                v-for="(photo, index) in form.existing_photos"
                                :key="index"
                                class="relative aspect-square rounded-lg overflow-hidden bg-gray-100"
                            >
                                <img
                                    :src="`/storage/${photo}`"
                                    :alt="`Foto ${index + 1}`"
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeExistingPhoto(index)"
                                    class="absolute top-1 right-1 rounded-full bg-red-600 p-1 text-white hover:bg-red-700"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div>
                        <InputLabel for="photos" value="Upload Foto Dokumentasi (Max 5 foto)" />
                        <input
                            id="photos"
                            type="file"
                            accept="image/*"
                            multiple
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            @change="handlePhotoChange"
                        />
                        <p class="mt-1 text-xs text-gray-500">Max 2MB per foto</p>
                        <InputError :message="form.errors.photos" class="mt-2" />

                        <!-- Photo Preview -->
                        <div v-if="photoPreview.length > 0" class="mt-2 grid grid-cols-5 gap-2">
                            <div
                                v-for="(preview, index) in photoPreview"
                                :key="index"
                                class="aspect-square rounded-lg overflow-hidden bg-gray-100"
                            >
                                <img
                                    :src="preview"
                                    :alt="`Preview ${index + 1}`"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Stats -->
                    <div>
                        <InputLabel value="Statistik Kehadiran" />
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-xs text-gray-600">Hadir</label>
                                <TextInput
                                    v-model.number="form.attendance_stats.present"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Sakit</label>
                                <TextInput
                                    v-model.number="form.attendance_stats.sick"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Izin</label>
                                <TextInput
                                    v-model.number="form.attendance_stats.permission"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Alpa</label>
                                <TextInput
                                    v-model.number="form.attendance_stats.absent"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                            </div>
                        </div>
                        <p v-if="totalAttendance > 0" class="mt-2 text-sm text-gray-600">
                            Total: {{ totalAttendance }} siswa ({{ attendancePercentage }}% hadir)
                        </p>
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan Tambahan" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            rows="2"
                            placeholder="Catatan atau informasi tambahan..."
                        ></textarea>
                        <InputError :message="form.errors.notes" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <SecondaryButton @click="closeModal" type="button">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            {{ isEditing ? 'Perbarui' : 'Simpan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Apakah Anda yakin ingin menghapus berita acara ini?
                    Foto-foto yang terkait juga akan dihapus.
                </p>
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="deleteJournal">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </TeacherLayout>
</template>
