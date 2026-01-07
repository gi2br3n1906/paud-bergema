<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    children: Array,
    selectedChild: Object,
    timeline: Array,
    stats: Object,
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

    return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`
}

const changeChild = (childId) => {
    router.get(route('parent.dashboard'), { child_id: childId }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const getAttendanceColor = (status) => {
    const colors = {
        'Hadir': 'bg-green-100 text-green-800',
        'Sakit': 'bg-yellow-100 text-yellow-800',
        'Izin': 'bg-blue-100 text-blue-800',
        'Alpa': 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const getMoodEmoji = (mood) => {
    const emojis = {
        'Senang': '😊',
        'Biasa': '😐',
        'Sedih': '😢',
        'Rewel': '😠',
    }
    return emojis[mood] || '😐'
}
</script>

<template>
    <Head title="Dashboard Wali Murid" />

    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <h1 class="text-xl font-bold text-indigo-600">Unit PAUD - Wali Murid</h1>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-600 hover:text-gray-900 font-medium"
                    >
                        Logout
                    </Link>
                </div>
            </div>
        </nav>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Welcome Card -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-2xl font-bold text-white">Selamat Datang, {{ $page.props.auth.user.name }}!</h2>
                    <p class="text-indigo-100 mt-1">Pantau perkembangan dan kegiatan anak Anda</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 rounded-full">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Anak</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_children }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Jurnal Kelas</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.class_journals_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Log Harian</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.daily_logs_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Child Selector (if multiple children) -->
                <div v-if="children.length > 1" class="bg-white rounded-lg shadow p-4 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Anak:</label>
                    <select
                        @change="changeChild($event.target.value)"
                        :value="selectedChild?.id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="child in children" :key="child.id" :value="child.id">
                            {{ child.name }} - {{ child.classroom?.name || 'Belum ada kelas' }}
                        </option>
                    </select>
                </div>

                <!-- Selected Child Info -->
                <div v-if="selectedChild" class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Informasi Anak</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Lengkap</p>
                            <p class="font-medium text-gray-900">{{ selectedChild.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Nama Panggilan</p>
                            <p class="font-medium text-gray-900">{{ selectedChild.nickname || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kelas</p>
                            <p class="font-medium text-gray-900">{{ selectedChild.classroom?.name || 'Belum ada kelas' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tahun Ajaran</p>
                            <p class="font-medium text-gray-900">{{ selectedChild.classroom?.academic_year?.name || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline Kegiatan (30 Hari Terakhir)</h3>

                    <div v-if="timeline.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Belum ada kegiatan tercatat</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="item in timeline"
                            :key="`${item.type}-${item.id}`"
                            class="border rounded-lg p-4"
                            :class="item.type === 'class_journal' ? 'border-blue-200 bg-blue-50' : 'border-purple-200 bg-purple-50'"
                        >
                            <!-- Class Journal -->
                            <template v-if="item.type === 'class_journal'">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Jurnal Kelas
                                            </span>
                                            <span class="text-sm font-medium text-gray-900">{{ item.title }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(item.date) }}</p>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <p class="text-sm text-gray-700 mb-2"><strong>Kelas:</strong> {{ item.classroom }}</p>
                                    <p class="text-sm text-gray-700 mb-2"><strong>Guru:</strong> {{ item.teacher }}</p>
                                    <p class="text-sm text-gray-700"><strong>Kegiatan:</strong> {{ item.description }}</p>
                                </div>

                                <!-- Attendance Stats -->
                                <div v-if="item.attendance_stats" class="mt-3 grid grid-cols-4 gap-2">
                                    <div class="bg-white rounded p-2 text-center">
                                        <p class="text-xs text-gray-600">Hadir</p>
                                        <p class="text-lg font-semibold text-green-600">{{ item.attendance_stats.present || 0 }}</p>
                                    </div>
                                    <div class="bg-white rounded p-2 text-center">
                                        <p class="text-xs text-gray-600">Sakit</p>
                                        <p class="text-lg font-semibold text-yellow-600">{{ item.attendance_stats.sick || 0 }}</p>
                                    </div>
                                    <div class="bg-white rounded p-2 text-center">
                                        <p class="text-xs text-gray-600">Izin</p>
                                        <p class="text-lg font-semibold text-blue-600">{{ item.attendance_stats.permission || 0 }}</p>
                                    </div>
                                    <div class="bg-white rounded p-2 text-center">
                                        <p class="text-xs text-gray-600">Alpa</p>
                                        <p class="text-lg font-semibold text-red-600">{{ item.attendance_stats.absent || 0 }}</p>
                                    </div>
                                </div>

                                <!-- Photos -->
                                <div v-if="item.photos && item.photos.length > 0" class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Foto Kegiatan:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        <img
                                            v-for="(photo, index) in item.photos"
                                            :key="index"
                                            :src="`/storage/${photo}`"
                                            :alt="`Foto ${index + 1}`"
                                            class="w-full h-32 object-cover rounded-lg"
                                        >
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div v-if="item.notes" class="mt-3 bg-white rounded p-3">
                                    <p class="text-sm font-medium text-gray-700 mb-1">Catatan:</p>
                                    <p class="text-sm text-gray-600">{{ item.notes }}</p>
                                </div>
                            </template>

                            <!-- Daily Log -->
                            <template v-else-if="item.type === 'daily_log'">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Log Harian
                                            </span>
                                            <span class="text-sm font-medium text-gray-900">{{ item.title }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(item.date) }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700">Kehadiran:</span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getAttendanceColor(item.attendance_status)"
                                        >
                                            {{ item.attendance_status }}
                                        </span>
                                        <span v-if="item.arrival_time" class="text-sm text-gray-600">
                                            ({{ item.arrival_time }})
                                        </span>
                                    </div>

                                    <div v-if="item.mood" class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-700">Mood:</span>
                                        <span class="text-lg">{{ getMoodEmoji(item.mood) }}</span>
                                        <span class="text-sm text-gray-600">{{ item.mood }}</span>
                                    </div>

                                    <div v-if="item.activities">
                                        <p class="text-sm font-medium text-gray-700 mb-1">Kegiatan:</p>
                                        <p class="text-sm text-gray-600">{{ item.activities }}</p>
                                    </div>

                                    <div v-if="item.notes" class="bg-white rounded p-3">
                                        <p class="text-sm font-medium text-gray-700 mb-1">Catatan Guru:</p>
                                        <p class="text-sm text-gray-600">{{ item.notes }}</p>
                                    </div>

                                    <p class="text-xs text-gray-500 mt-2">Dicatat oleh: {{ item.teacher }}</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
