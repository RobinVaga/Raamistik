<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import MapView from '@/components/MapView.vue';

interface Marker {
    id: number;
    name: string;
    lat: number;
    lng: number;
    description: string | null;
    created_at: string;
    updated_at: string;
}

defineProps<{
    markers: Marker[];
}>();
</script>

<template>
    <Head title="Kaart" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-black overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-2xl font-bold text-black">Interaktiivne Kaart</h2>
                                <p class="text-white">Kliki kaardil, et lisada uus marker</p>
                            </div>
                            <div class="text-sm text-white">
                                Markereid kokku: {{ markers.length }}
                            </div>
                        </div>

                        <div class="h-[600px] rounded-lg border-2 border-gray-300 shadow-lg overflow-hidden">
                            <MapView />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>