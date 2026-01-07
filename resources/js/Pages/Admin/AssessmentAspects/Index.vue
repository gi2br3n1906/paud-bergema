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
    aspects: Object,
    categories: Array,
    filters: Object
})

const showModal = ref(false)
const isEditing = ref(false)
const aspectToDelete = ref(null)
const showDeleteModal = ref(false)

const searchForm = useForm({
    search: props.filters.search || '',
    category: props.filters.category || ''
})

const form = useForm({
    id: null,
    name: '',
    category: '',
    description: ''
})

// Debounced search
const performSearch = debounce(() => {
    searchForm.get(route('admin.assessment-aspects.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['aspects', 'filters']
    })
}, 300)

watch(() => searchForm.search, () => {
    performSearch()
})

watch(() => searchForm.category, () => {
    searchForm.get(route('admin.assessment-aspects.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['aspects', 'filters']
    })
})

const openCreateModal = () => {
    isEditing.value = false
    form.reset()
    form.clearErrors()
    showModal.value = true
}

const openEditModal = (aspect) => {
    isEditing.value = true
    form.id = aspect.id
    form.name = aspect.name
    form.category = aspect.category
    form.description = aspect.description || ''
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
        form.put(route('admin.assessment-aspects.update', form.id), {
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('admin.assessment-aspects.store'), {
            onSuccess: () => closeModal()
        })
    }
}

const confirmDelete = (aspect) => {
    aspectToDelete.value = aspect
    showDeleteModal.value = true
}

const deleteAspect = () => {
    router.delete(route('admin.assessment-aspects.destroy', aspectToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            aspectToDelete.value = null
        }
    })
}

const resetFilters = () => {
    searchForm.search = ''
    searchForm.category = ''
}

// Common assessment categories
const commonCategories = [
    'Nilai Agama dan Moral',
    'Fisik Motorik',
    'Kognitif',
    'Bahasa',
    'Sosial Emosional',
    'Seni'
]
</script>

<template>
    <Head title="Aspek Penilaian" />

    <AdminLayout>
        <template #header>
            Aspek Penilaian
        </template>

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Kelola Aspek Penilaian</h2>
                    <p class="mt-1 text-sm text-gray-600">Aspek penilaian untuk rapor siswa</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    + Tambah Aspek
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
                            placeholder="Nama atau deskripsi..."
                            class="mt-1 block w-full"
                        />
                    </div>
                    <div>
                        <InputLabel value="Kategori" />
                        <select
                            v-model="searchForm.category"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <SecondaryButton @click="resetFilters" class="w-full">
                            Reset Filter
                        </SecondaryButton>
                    </div>
                </div>
            </div>

            <!-- Aspects Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kategori
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Aspek
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Deskripsi
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="aspects.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data aspek penilaian.
                            </td>
                        </tr>
                        <tr v-for="aspect in aspects.data" :key="aspect.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ aspect.category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ aspect.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 line-clamp-2">
                                    {{ aspect.description || '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button
                                    @click="openEditModal(aspect)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDelete(aspect)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="aspects.data.length > 0" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan {{ aspects.from }} - {{ aspects.to }} dari {{ aspects.total }} data
                        </div>
                        <div class="flex gap-2">
                            <SecondaryButton
                                v-for="link in aspects.links"
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
                    {{ isEditing ? 'Edit Aspek Penilaian' : 'Tambah Aspek Penilaian Baru' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Aspek" required />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Contoh: Mengenal perilaku yang baik dan sopan"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="category" value="Kategori" required />
                        <div class="mt-1 flex gap-2">
                            <select
                                id="category"
                                v-model="form.category"
                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="">-- Pilih Kategori --</option>
                                <option v-for="cat in commonCategories" :key="cat" :value="cat">
                                    {{ cat }}
                                </option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Pilih kategori dari daftar atau ketik manual jika berbeda
                        </p>
                        <InputError :message="form.errors.category" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Deskripsi" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Deskripsi detail tentang aspek penilaian ini..."
                        ></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
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
                    Apakah Anda yakin ingin menghapus aspek penilaian <strong>{{ aspectToDelete?.name }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="deleteAspect">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
