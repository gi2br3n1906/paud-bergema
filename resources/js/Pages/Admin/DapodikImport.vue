<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    sampleCsvUrl: String,
    importResults: Object,
})

const fileInput = ref(null)
const selectedFileName = ref('')

const form = useForm({
    file: null,
})

const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.file = file
        selectedFileName.value = file.name
    }
}

const submitImport = () => {
    form.post(route('admin.dapodik-import.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset()
            selectedFileName.value = ''
            if (fileInput.value) {
                fileInput.value.value = ''
            }
        }
    })
}
</script>

<template>
    <Head title="Import Data Dapodik" />

    <AdminLayout>
        <template #header>
            Import Data Dapodik
        </template>

        <div class="space-y-6">
            <!-- Info Card -->
            <div class="rounded-lg bg-blue-50 border border-blue-200 p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-blue-900">Import Data Siswa & Orang Tua dari Dapodik</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p class="mb-2">Fitur ini memungkinkan Anda untuk mengimport data siswa dan orang tua secara massal dari file CSV format Dapodik.</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>File harus dalam format CSV (Comma Separated Values)</li>
                                <li>Maksimal ukuran file: 10 MB</li>
                                <li>Sistem akan otomatis membuat akun orang tua dengan password = tanggal lahir anak (format: DDMMYYYY)</li>
                                <li>Jika nomor HP sudah terdaftar, siswa akan ditautkan ke akun orang tua yang sudah ada</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div v-if="$page.props.flash.success" class="rounded-lg bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ $page.props.flash.success }}</p>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="$page.props.flash.error" class="rounded-lg bg-red-50 border border-red-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ $page.props.flash.error }}</p>
                    </div>
                </div>
            </div>

            <!-- Import Results Details -->
            <div v-if="importResults && importResults.errors && importResults.errors.length > 0" class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">Detail Error:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700">
                    <li v-for="(error, index) in importResults.errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <!-- Upload Form -->
            <div class="rounded-lg bg-white shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload File CSV</h2>

                <form @submit.prevent="submitImport" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih File CSV
                        </label>

                        <div class="flex items-center space-x-4">
                            <label class="flex-1 cursor-pointer">
                                <div class="flex items-center justify-center px-6 py-4 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600">
                                            <span class="font-semibold text-blue-600">Klik untuk upload</span>
                                            atau drag & drop
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">CSV hingga 10MB</p>
                                    </div>
                                </div>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".csv,text/csv"
                                    class="hidden"
                                    @change="handleFileChange"
                                />
                            </label>
                        </div>

                        <p v-if="selectedFileName" class="mt-2 text-sm text-gray-600">
                            File terpilih: <span class="font-medium text-gray-900">{{ selectedFileName }}</span>
                        </p>

                        <InputError :message="form.errors.file" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <a
                            :href="sampleCsvUrl"
                            download
                            class="text-sm text-blue-600 hover:text-blue-800 underline"
                        >
                            Download Template CSV
                        </a>

                        <PrimaryButton
                            type="submit"
                            :disabled="!form.file || form.processing"
                            :class="{ 'opacity-50': !form.file || form.processing }"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Mengimport...' : 'Import Data' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Format Guide -->
            <div class="rounded-lg bg-white shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Format File CSV</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kolom</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contoh</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">NISN *</td>
                                <td class="px-4 py-2 text-gray-600">Nomor Induk Siswa Nasional (wajib, unik)</td>
                                <td class="px-4 py-2 text-gray-600">1234567890</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Nama Lengkap *</td>
                                <td class="px-4 py-2 text-gray-600">Nama lengkap siswa (wajib)</td>
                                <td class="px-4 py-2 text-gray-600">Ahmad Fauzi</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Nama Panggilan</td>
                                <td class="px-4 py-2 text-gray-600">Nama panggilan (opsional)</td>
                                <td class="px-4 py-2 text-gray-600">Fauzi</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Jenis Kelamin *</td>
                                <td class="px-4 py-2 text-gray-600">L atau P (wajib)</td>
                                <td class="px-4 py-2 text-gray-600">L</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Tanggal Lahir *</td>
                                <td class="px-4 py-2 text-gray-600">Format: DD/MM/YYYY (wajib)</td>
                                <td class="px-4 py-2 text-gray-600">15/08/2019</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Tempat Lahir</td>
                                <td class="px-4 py-2 text-gray-600">Tempat lahir siswa (opsional)</td>
                                <td class="px-4 py-2 text-gray-600">Jakarta</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Alamat</td>
                                <td class="px-4 py-2 text-gray-600">Alamat lengkap (opsional)</td>
                                <td class="px-4 py-2 text-gray-600">Jl. Merdeka No. 123</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Nama Orang Tua/Wali</td>
                                <td class="px-4 py-2 text-gray-600">Nama lengkap orang tua</td>
                                <td class="px-4 py-2 text-gray-600">Bapak Santoso</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">No HP Orang Tua</td>
                                <td class="px-4 py-2 text-gray-600">Nomor HP (untuk login)</td>
                                <td class="px-4 py-2 text-gray-600">08123456789</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">Hubungan</td>
                                <td class="px-4 py-2 text-gray-600">Ayah/Ibu/Wali</td>
                                <td class="px-4 py-2 text-gray-600">Ayah</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 text-sm text-gray-600">
                    <span class="text-red-600">*</span> = Kolom wajib diisi
                </p>
            </div>
        </div>
    </AdminLayout>
</template>
