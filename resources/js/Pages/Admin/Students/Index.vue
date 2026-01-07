<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    students: Object,
    classrooms: Array,
    parents: Array,
    filters: Object
})

const showModal = ref(false)
const isEditing = ref(false)
const studentToDelete = ref(null)
const showDeleteModal = ref(false)

const form = useForm({
    id: null,
    nisn: '',
    name: '',
    nickname: '',
    date_of_birth: '',
    place_of_birth: '',
    gender: 'Laki-laki',
    address: '',
    photo: null,
    classroom_id: null,
    enrollment_date: new Date().toISOString().split('T')[0],
    status: 'Aktif',
    notes: '',
    parent_ids: []
})

const searchForm = useForm({
    search: props.filters.search || '',
    classroom_id: props.filters.classroom_id || '',
    is_active: props.filters.is_active ?? ''
})

const openCreateModal = () => {
    isEditing.value = false
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (student) => {
    isEditing.value = true
    form.id = student.id
    form.nisn = student.nisn
    form.name = student.name
    form.nickname = student.nickname || ''
    form.date_of_birth = student.date_of_birth
    form.place_of_birth = student.place_of_birth || ''
    form.gender = student.gender
    form.address = student.address || ''
    form.classroom_id = student.classroom_id
    form.enrollment_date = student.enrollment_date
    form.status = student.status
    form.notes = student.notes || ''
    form.parent_ids = student.parents?.map(p => p.id) || []
    form.clearErrors()
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.reset()
    form.clearErrors()
}

const handlePhotoChange = (event) => {
    form.photo = event.target.files[0]
}

const submitForm = () => {
    if (isEditing.value) {
        form.post(route('admin.students.update', form.id), {
            forceFormData: true,
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('admin.students.store'), {
            forceFormData: true,
            onSuccess: () => closeModal()
        })
    }
}

const confirmDelete = (student) => {
    studentToDelete.value = student
    showDeleteModal.value = true
}

const deleteStudent = () => {
    router.delete(route('admin.students.destroy', studentToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            studentToDelete.value = null
        }
    })
}

const search = () => {
    searchForm.get(route('admin.students.index'), {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    searchForm.reset()
    search()
}

const getAge = (dateOfBirth) => {
    const today = new Date()
    const birthDate = new Date(dateOfBirth)
    let age = today.getFullYear() - birthDate.getFullYear()
    const monthDiff = today.getMonth() - birthDate.getMonth()
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--
    }
    return age
}
</script>

<template>
    <Head title="Kelola Siswa" />

    <AdminLayout>
        <template #header>
            Kelola Siswa
        </template>

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Daftar Siswa</h2>
                    <p class="mt-1 text-sm text-gray-600">Kelola data siswa PAUD</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Siswa
                </PrimaryButton>
            </div>

            <!-- Filters -->
            <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <InputLabel for="search" value="Cari" />
                        <TextInput
                            id="search"
                            v-model="searchForm.search"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Nama siswa..."
                            @keyup.enter="search"
                        />
                    </div>
                    <div>
                        <InputLabel for="classroom" value="Kelas" />
                        <select
                            id="classroom"
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
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="searchForm.is_active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <PrimaryButton @click="search" class="flex-1">Cari</PrimaryButton>
                        <SecondaryButton @click="clearFilters">Reset</SecondaryButton>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="rounded-lg bg-white shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Siswa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Kelas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jenis Kelamin
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Usia
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Orang Tua
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="students.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Tidak ada data siswa.
                                </td>
                            </tr>
                            <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div v-if="student.photo_url" class="h-10 w-10 rounded-full overflow-hidden">
                                                <img :src="`/storage/${student.photo_url}`" :alt="student.name" class="h-full w-full object-cover">
                                            </div>
                                            <div v-else class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-700">
                                                    {{ student.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                                            <div v-if="student.nickname" class="text-sm text-gray-500">{{ student.nickname }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ student.classroom?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ student.gender }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ getAge(student.date_of_birth) }} tahun
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div v-if="student.parents?.length > 0">
                                        <div v-for="parent in student.parents" :key="parent.id" class="text-xs">
                                            {{ parent.name }}
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                            student.status === 'Aktif'
                                                ? 'bg-green-100 text-green-800'
                                                : student.status === 'Lulus'
                                                ? 'bg-blue-100 text-blue-800'
                                                : student.status === 'Pindah'
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ student.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        @click="openEditModal(student)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete(student)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="students.links.length > 3" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ students.from }} to {{ students.to }} of {{ students.total }} results
                        </div>
                        <div class="flex space-x-1">
                            <Link
                                v-for="(link, index) in students.links"
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
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Edit Siswa' : 'Tambah Siswa Baru' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="nisn" value="NISN" />
                            <TextInput
                                id="nisn"
                                v-model="form.nisn"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="20"
                            />
                            <InputError :message="form.errors.nisn" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Nama Lengkap *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="nickname" value="Nama Panggilan" />
                            <TextInput
                                id="nickname"
                                v-model="form.nickname"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.nickname" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="gender" value="Jenis Kelamin *" />
                            <select
                                id="gender"
                                v-model="form.gender"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            <InputError :message="form.errors.gender" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="date_of_birth" value="Tanggal Lahir *" />
                            <TextInput
                                id="date_of_birth"
                                v-model="form.date_of_birth"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.date_of_birth" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="place_of_birth" value="Tempat Lahir" />
                            <TextInput
                                id="place_of_birth"
                                v-model="form.place_of_birth"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.place_of_birth" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="address" value="Alamat" />
                        <textarea
                            id="address"
                            v-model="form.address"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            rows="2"
                        ></textarea>
                        <InputError :message="form.errors.address" class="mt-2" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">

                        <div>
                            <InputLabel for="classroom_id" value="Kelas" />
                            <select
                                id="classroom_id"
                                v-model="form.classroom_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option :value="null">Belum ada kelas</option>
                                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                                    {{ classroom.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.classroom_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="enrollment_date" value="Tanggal Daftar *" />
                            <TextInput
                                id="enrollment_date"
                                v-model="form.enrollment_date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.enrollment_date" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="photo" value="Foto Siswa" />
                            <input
                                id="photo"
                                type="file"
                                accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                @change="handlePhotoChange"
                            />
                            <InputError :message="form.errors.photo" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status *" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required
                            >
                                <option value="Aktif">Aktif</option>
                                <option value="Lulus">Lulus</option>
                                <option value="Pindah">Pindah</option>
                                <option value="Keluar">Keluar</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="parent_ids" value="Orang Tua / Wali" />
                        <select
                            id="parent_ids"
                            v-model="form.parent_ids"
                            multiple
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            size="4"
                        >
                            <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                                {{ parent.name }} ({{ parent.email }})
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Tahan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu</p>
                        <InputError :message="form.errors.parent_ids" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            rows="3"
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
                    Apakah Anda yakin ingin menghapus siswa <strong>{{ studentToDelete?.name }}</strong>?
                    Data yang sudah dihapus tidak dapat dikembalikan.
                </p>
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="deleteStudent">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
