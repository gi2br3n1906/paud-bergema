<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    classrooms: Array,
    academicYears: Array,
    teachers: Array,
    filters: Object
})

const showModal = ref(false)
const isEditing = ref(false)
const classroomToDelete = ref(null)
const showDeleteModal = ref(false)

const form = useForm({
    id: null,
    name: '',
    level: '',
    academic_year_id: null,
    teacher_id: null,
    capacity: 20,
    description: ''
})

const filterForm = useForm({
    academic_year_id: props.filters.academic_year_id || ''
})

const openCreateModal = () => {
    isEditing.value = false
    form.reset()
    // Set default academic year to active one
    const activeYear = props.academicYears.find(y => y.is_active)
    form.academic_year_id = activeYear?.id || null
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (classroom) => {
    isEditing.value = true
    form.id = classroom.id
    form.name = classroom.name
    form.level = classroom.level
    form.academic_year_id = classroom.academic_year_id
    form.teacher_id = classroom.teacher_id
    form.capacity = classroom.capacity
    form.description = classroom.description || ''
    form.clearErrors()
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.reset()
    form.clearErrors()
}

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.classrooms.update', form.id), {
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('admin.classrooms.store'), {
            onSuccess: () => closeModal()
        })
    }
}

const confirmDelete = (classroom) => {
    classroomToDelete.value = classroom
    showDeleteModal.value = true
}

const deleteClassroom = () => {
    router.delete(route('admin.classrooms.destroy', classroomToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            classroomToDelete.value = null
        }
    })
}

const filterByYear = () => {
    filterForm.get(route('admin.classrooms.index'), {
        preserveState: true,
        replace: true
    })
}
</script>

<template>
    <Head title="Kelola Kelas" />

    <AdminLayout>
        <template #header>
            Kelola Kelas
        </template>

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Daftar Kelas</h2>
                    <p class="mt-1 text-sm text-gray-600">Kelola kelas dan wali kelas</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Kelas
                </PrimaryButton>
            </div>

            <!-- Filter -->
            <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <InputLabel for="filter_year" value="Filter Tahun Ajaran" />
                        <select
                            id="filter_year"
                            v-model="filterForm.academic_year_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @change="filterByYear"
                        >
                            <option value="">Semua Tahun Ajaran</option>
                            <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                {{ year.name }} {{ year.is_active ? '(Aktif)' : '' }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Classrooms Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="classroom in classrooms"
                    :key="classroom.id"
                    class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow"
                >
                    <!-- Header -->
                    <div class="mb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ classroom.name }}</h3>
                                <p class="text-sm text-gray-500">{{ classroom.level }}</p>
                            </div>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                {{ classroom.academic_year?.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Teacher Info -->
                    <div class="mb-4 rounded-lg bg-gray-50 p-3">
                        <p class="text-xs font-medium text-gray-500">Wali Kelas</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">
                            {{ classroom.teacher?.name || 'Belum ditentukan' }}
                        </p>
                    </div>

                    <!-- Stats -->
                    <div class="mb-4 grid grid-cols-2 gap-3">
                        <div class="rounded bg-purple-50 p-3">
                            <p class="text-xs text-purple-600">Siswa</p>
                            <p class="text-xl font-bold text-purple-700">
                                {{ classroom.students_count || 0 }}/{{ classroom.capacity }}
                            </p>
                        </div>
                        <div class="rounded bg-green-50 p-3">
                            <p class="text-xs text-green-600">Kapasitas</p>
                            <p class="text-xl font-bold text-green-700">{{ classroom.capacity }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="classroom.description" class="mb-4">
                        <p class="text-xs text-gray-600 line-clamp-2">{{ classroom.description }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button
                            @click="openEditModal(classroom)"
                            class="flex-1 rounded bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                        >
                            Edit
                        </button>
                        <button
                            @click="confirmDelete(classroom)"
                            class="rounded bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                        >
                            Hapus
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="classrooms.length === 0"
                    class="col-span-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada kelas</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan kelas pertama.</p>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Edit Kelas' : 'Tambah Kelas Baru' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="name" value="Nama Kelas *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Contoh: Kelas A"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="level" value="Tingkat *" />
                            <select
                                id="level"
                                v-model="form.level"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="">Pilih Tingkat</option>
                                <option value="TK A">TK A</option>
                                <option value="TK B">TK B</option>
                                <option value="Playgroup">Playgroup</option>
                                <option value="KB">KB (Kelompok Bermain)</option>
                            </select>
                            <InputError :message="form.errors.level" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="academic_year_id" value="Tahun Ajaran *" />
                            <select
                                id="academic_year_id"
                                v-model="form.academic_year_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option :value="null">Pilih Tahun Ajaran</option>
                                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                                    {{ year.name }} {{ year.is_active ? '(Aktif)' : '' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.academic_year_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="teacher_id" value="Wali Kelas" />
                            <select
                                id="teacher_id"
                                v-model="form.teacher_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option :value="null">Belum ada wali kelas</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                    {{ teacher.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.teacher_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="capacity" value="Kapasitas Siswa *" />
                            <TextInput
                                id="capacity"
                                v-model.number="form.capacity"
                                type="number"
                                class="mt-1 block w-full"
                                min="1"
                                max="100"
                                required
                            />
                            <InputError :message="form.errors.capacity" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="description" value="Deskripsi" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            rows="3"
                            placeholder="Informasi tambahan tentang kelas..."
                        ></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
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
                    Apakah Anda yakin ingin menghapus kelas <strong>{{ classroomToDelete?.name }}</strong>?
                    Kelas yang memiliki siswa tidak dapat dihapus.
                </p>
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="deleteClassroom">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
