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
    academicYears: Array
})

const showModal = ref(false)
const isEditing = ref(false)
const yearToDelete = ref(null)
const showDeleteModal = ref(false)

const form = useForm({
    id: null,
    name: '',
    start_date: '',
    end_date: '',
    is_active: false
})

const openCreateModal = () => {
    isEditing.value = false
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (year) => {
    isEditing.value = true
    form.id = year.id
    form.name = year.name
    form.start_date = year.start_date
    form.end_date = year.end_date
    form.is_active = year.is_active
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
        form.put(route('admin.academic-years.update', form.id), {
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('admin.academic-years.store'), {
            onSuccess: () => closeModal()
        })
    }
}

const confirmDelete = (year) => {
    yearToDelete.value = year
    showDeleteModal.value = true
}

const deleteYear = () => {
    router.delete(route('admin.academic-years.destroy', yearToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            yearToDelete.value = null
        }
    })
}

const activateYear = (year) => {
    router.post(route('admin.academic-years.activate', year.id))
}
</script>

<template>
    <Head title="Tahun Ajaran" />

    <AdminLayout>
        <template #header>
            Tahun Ajaran
        </template>

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Kelola Tahun Ajaran</h2>
                    <p class="mt-1 text-sm text-gray-600">Atur tahun ajaran dan semester</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Tahun Ajaran
                </PrimaryButton>
            </div>

            <!-- Academic Years Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="year in academicYears"
                    :key="year.id"
                    :class="[
                        'rounded-lg border-2 bg-white p-6 shadow-sm transition-all',
                        year.is_active
                            ? 'border-green-500 shadow-md'
                            : 'border-gray-200'
                    ]"
                >
                    <!-- Active Badge -->
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">{{ year.name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ new Date(year.start_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                -
                                {{ new Date(year.end_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </p>
                        </div>
                        <span
                            v-if="year.is_active"
                            class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800"
                        >
                            Aktif
                        </span>
                    </div>

                    <!-- Stats -->
                    <div class="mb-4 grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4">
                        <div>
                            <p class="text-xs text-gray-500">Semester</p>
                            <p class="text-2xl font-bold text-gray-900">{{ year.academic_terms_count || 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Kelas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ year.classrooms_count || 0 }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="!year.is_active"
                            @click="activateYear(year)"
                            class="flex-1 rounded bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100"
                        >
                            Aktifkan
                        </button>
                        <button
                            @click="openEditModal(year)"
                            class="flex-1 rounded bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                        >
                            Edit
                        </button>
                        <button
                            @click="confirmDelete(year)"
                            class="rounded bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                        >
                            Hapus
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="academicYears.length === 0"
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
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada tahun ajaran</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan tahun ajaran pertama.</p>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Tahun Ajaran *" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Contoh: 2024/2025"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="start_date" value="Tanggal Mulai *" />
                            <TextInput
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.start_date" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="end_date" value="Tanggal Selesai *" />
                            <TextInput
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.end_date" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Aktifkan tahun ajaran ini
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">
                        *Hanya satu tahun ajaran yang dapat aktif pada satu waktu
                    </p>
                    <InputError :message="form.errors.is_active" class="mt-2" />

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
                    Apakah Anda yakin ingin menghapus tahun ajaran <strong>{{ yearToDelete?.name }}</strong>?
                    Semua semester yang terkait akan ikut terhapus.
                </p>
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="deleteYear">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
