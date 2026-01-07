<script setup>
import { ref, watch } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { debounce } from 'lodash'

const props = defineProps({
    teachers: Object,
    filters: Object
})

const showModal = ref(false)
const isEditing = ref(false)
const teacherToDelete = ref(null)
const showDeleteModal = ref(false)

const searchForm = useForm({
    search: props.filters.search || '',
    is_active: props.filters.is_active ?? ''
})

const form = useForm({
    id: null,
    name: '',
    email: '',
    phone: '',
    address: '',
    password: '',
    password_confirmation: '',
    is_active: true
})

// Debounced search
const performSearch = debounce(() => {
    searchForm.get(route('admin.teachers.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['teachers', 'filters']
    })
}, 300)

watch(() => searchForm.search, () => {
    performSearch()
})

watch(() => searchForm.is_active, () => {
    searchForm.get(route('admin.teachers.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['teachers', 'filters']
    })
})

const openCreateModal = () => {
    isEditing.value = false
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (teacher) => {
    isEditing.value = true
    form.id = teacher.id
    form.name = teacher.name
    form.email = teacher.email
    form.phone = teacher.phone || ''
    form.address = teacher.address || ''
    form.password = ''
    form.password_confirmation = ''
    form.is_active = teacher.is_active
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
        form.put(route('admin.teachers.update', form.id), {
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('admin.teachers.store'), {
            onSuccess: () => closeModal()
        })
    }
}

const confirmDelete = (teacher) => {
    teacherToDelete.value = teacher
    showDeleteModal.value = true
}

const deleteTeacher = () => {
    router.delete(route('admin.teachers.destroy', teacherToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            teacherToDelete.value = null
        }
    })
}

const resetFilters = () => {
    searchForm.search = ''
    searchForm.is_active = ''
}
</script>

<template>
    <Head title="Data Guru" />

    <AdminLayout>
        <template #header>
            Data Guru
        </template>

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Kelola Data Guru</h2>
                    <p class="mt-1 text-sm text-gray-600">Manajemen akun dan informasi guru</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    + Tambah Guru
                </PrimaryButton>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Cari" />
                        <TextInput
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Nama, email, atau telepon..."
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel value="Status" />
                        <select
                            v-model="searchForm.is_active"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                        >
                            <option value="">Semua Status</option>
                            <option :value="1">Aktif</option>
                            <option :value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <SecondaryButton @click="resetFilters" class="w-full">
                            Reset Filter
                        </SecondaryButton>
                    </div>
                </div>
            </div>

            <!-- Teachers Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kontak
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Terdaftar
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="teachers.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data guru.
                            </td>
                        </tr>
                        <tr v-for="teacher in teachers.data" :key="teacher.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ teacher.name }}</div>
                                <div class="text-sm text-gray-500">{{ teacher.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ teacher.phone || '-' }}</div>
                                <div class="text-sm text-gray-500 line-clamp-1">{{ teacher.address || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        teacher.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800',
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                    ]"
                                >
                                    {{ teacher.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ new Date(teacher.created_at).toLocaleDateString('id-ID') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button
                                    @click="openEditModal(teacher)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDelete(teacher)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="teachers.data.length > 0" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan {{ teachers.from }} - {{ teachers.to }} dari {{ teachers.total }} data
                        </div>
                        <div class="flex gap-2">
                            <SecondaryButton
                                v-for="link in teachers.links"
                                :key="link.label"
                                @click="link.url && router.visit(link.url)"
                                :disabled="!link.url"
                                :class="{ 'bg-blue-500 text-white': link.active }"
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
                    {{ isEditing ? 'Edit Data Guru' : 'Tambah Guru Baru' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="name" value="Nama Lengkap" required />
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
                            <InputLabel for="email" value="Email" required />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="phone" value="Nomor Telepon" />
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.phone" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="is_active" value="Status" required />
                            <select
                                id="is_active"
                                v-model="form.is_active"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required
                            >
                                <option :value="true">Aktif</option>
                                <option :value="false">Nonaktif</option>
                            </select>
                            <InputError :message="form.errors.is_active" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="address" value="Alamat" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="2"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError :message="form.errors.address" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel
                                for="password"
                                :value="isEditing ? 'Password Baru (kosongkan jika tidak diubah)' : 'Password'"
                                :required="!isEditing"
                            />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                :required="!isEditing"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel
                                for="password_confirmation"
                                value="Konfirmasi Password"
                                :required="!isEditing || form.password"
                            />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                :required="!isEditing || form.password"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton type="button" @click="closeModal">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
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
                <p class="text-sm text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus guru <strong>{{ teacherToDelete?.name }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="deleteTeacher">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
