<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})
</script>

<template>
    <Head title="Selamat Datang - PKBM Bergema Unit PAUD" />

    <div class="min-h-screen bg-gradient-to-br from-yellow-50 via-orange-50 to-blue-50">
        <!-- Navbar -->
        <nav class="bg-white/80 backdrop-blur-sm shadow-sm sticky top-0 z-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-blue-500">
                                PKBM Bergema
                            </h1>
                        </div>
                    </div>

                    <!-- Menu Links (Center) -->
                    <div class="hidden md:block">
                        <div class="flex items-center space-x-8">
                            <a href="#beranda" class="text-gray-700 hover:text-orange-500 transition-colors font-medium">
                                Beranda
                            </a>
                            <a href="#tentang" class="text-gray-700 hover:text-orange-500 transition-colors font-medium">
                                Tentang
                            </a>
                            <a href="#guru" class="text-gray-700 hover:text-orange-500 transition-colors font-medium">
                                Guru
                            </a>
                            <a href="#kontak" class="text-gray-700 hover:text-orange-500 transition-colors font-medium">
                                Kontak
                            </a>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <div class="flex items-center">
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-full bg-gradient-to-r from-orange-400 to-orange-500 px-6 py-2 text-sm font-semibold text-white shadow-md hover:shadow-lg hover:from-orange-500 hover:to-orange-600 transition-all duration-300"
                        >
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section id="beranda" class="relative overflow-hidden py-20 sm:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block">Bermain, Belajar,</span>
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-orange-400 to-blue-400">
                            & Tumbuh Bersama
                        </span>
                    </h2>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 sm:text-xl">
                        Unit PAUD PKBM Bergema hadir dengan sistem pembelajaran modern yang ceria, 
                        dilengkapi teknologi AI untuk memantau perkembangan anak Anda.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-4">
                        <a
                            href="#tentang"
                            class="rounded-full bg-gradient-to-r from-orange-400 to-orange-500 px-8 py-4 text-lg font-semibold text-white shadow-lg hover:shadow-xl hover:from-orange-500 hover:to-orange-600 transition-all duration-300 transform hover:scale-105"
                        >
                            Pelajari Lebih Lanjut
                        </a>
                        <a
                            href="#kontak"
                            class="rounded-full bg-white px-8 py-4 text-lg font-semibold text-orange-500 shadow-md hover:shadow-lg border-2 border-orange-200 hover:border-orange-300 transition-all duration-300"
                        >
                            Hubungi Kami
                        </a>
                    </div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-200 rounded-full opacity-20 blur-2xl"></div>
                <div class="absolute bottom-10 right-10 w-32 h-32 bg-blue-200 rounded-full opacity-20 blur-3xl"></div>
            </div>
        </section>

        <!-- Feature Cards Section -->
        <section id="tentang" class="py-16 sm:py-24 bg-white/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h3 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                        Kenapa Memilih Kami?
                    </h3>
                    <p class="mt-4 text-lg text-gray-600">
                        Fasilitas dan layanan terbaik untuk tumbuh kembang buah hati Anda
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <!-- Card 1: Smart Rapor AI -->
                    <div class="group rounded-3xl bg-gradient-to-br from-yellow-50 to-yellow-100 p-8 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-yellow-200 text-yellow-600 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Smart Rapor AI</h4>
                        <p class="text-gray-600">
                            Rapor naratif yang dihasilkan dengan bantuan kecerdasan buatan untuk menggambarkan 
                            perkembangan anak secara personal dan detail.
                        </p>
                    </div>

                    <!-- Card 2: Monitoring Harian -->
                    <div class="group rounded-3xl bg-gradient-to-br from-orange-50 to-orange-100 p-8 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-200 text-orange-600 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Monitoring Harian</h4>
                        <p class="text-gray-600">
                            Pantau kehadiran, kegiatan ibadah, dan perkembangan mengaji anak Anda secara 
                            real-time melalui portal orang tua.
                        </p>
                    </div>

                    <!-- Card 3: Lingkungan Asri -->
                    <div class="group rounded-3xl bg-gradient-to-br from-blue-50 to-blue-100 p-8 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-200 text-blue-600 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Lingkungan Asri</h4>
                        <p class="text-gray-600">
                            Fasilitas bermain yang aman dan nyaman dengan area hijau yang luas, 
                            mendukung eksplorasi dan kreativitas anak.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Teacher Section (Preview) -->
        <section id="guru" class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h3 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                        Guru Berpengalaman & Bersertifikat
                    </h3>
                    <p class="mt-4 text-lg text-gray-600">
                        Tim pengajar profesional yang siap mendampingi tumbuh kembang anak
                    </p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-yellow-200 to-yellow-300 shadow-md"></div>
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-orange-200 to-orange-300 shadow-md"></div>
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-200 to-blue-300 shadow-md"></div>
                    </div>
                    <p class="mt-6 text-gray-600">
                        <Link 
                            :href="route('login')"
                            class="text-orange-500 font-semibold hover:text-orange-600 transition-colors"
                        >
                            Login untuk melihat profil lengkap guru →
                        </Link>
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-gradient-to-r from-orange-400 to-blue-400">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h3 class="text-3xl font-bold text-white sm:text-4xl">
                    Siap Bergabung Bersama Kami?
                </h3>
                <p class="mt-4 text-lg text-white/90">
                    Daftarkan anak Anda sekarang dan rasakan pengalaman pembelajaran yang menyenangkan!
                </p>
                <div class="mt-8">
                    <a
                        href="#kontak"
                        class="inline-block rounded-full bg-white px-8 py-4 text-lg font-semibold text-orange-500 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                    >
                        Hubungi Kami Sekarang
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer id="kontak" class="bg-gray-900 text-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-3">
                    <div>
                        <h4 class="text-xl font-bold mb-4">PKBM Bergema</h4>
                        <p class="text-gray-400">
                            Unit PAUD dengan sistem pembelajaran modern dan berbasis teknologi.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Kontak</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>📞 +62 812-3456-7890</li>
                            <li>📧 info@pkbmbergema.sch.id</li>
                            <li>📍 Jl. Pendidikan No. 123, Kota</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Link Cepat</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="#beranda" class="text-gray-400 hover:text-orange-400 transition-colors">
                                    Beranda
                                </a>
                            </li>
                            <li>
                                <a href="#tentang" class="text-gray-400 hover:text-orange-400 transition-colors">
                                    Tentang Kami
                                </a>
                            </li>
                            <li>
                                <Link 
                                    :href="route('login')" 
                                    class="text-gray-400 hover:text-orange-400 transition-colors"
                                >
                                    Portal Login
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 border-t border-gray-800 pt-8 text-center">
                    <p class="text-gray-400">
                        &copy; {{ new Date().getFullYear() }} PKBM Bergema - Unit PAUD. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
