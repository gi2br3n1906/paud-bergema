<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Dashboard Wali Murid" />

    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <h1 class="text-xl font-bold">Unit PAUD - Wali Murid</h1>
                    <Link :href="route('logout')" method="post" as="button" class="text-sm text-gray-600 hover:text-gray-900">
                        Logout
                    </Link>
                </div>
            </div>
        </nav>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium">Selamat Datang, Orang Tua!</h3>
                        <p class="mt-2 text-gray-600">
                            Dashboard wali murid akan dikembangkan pada fase berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
