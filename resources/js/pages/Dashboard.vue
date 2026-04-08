<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { WeatherData, type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import PlaceholderPattern from '../components/PlaceholderPattern.vue'
import { Map, BookOpen, ShoppingCart } from 'lucide-vue-next'
import Mapview from '@/components/MapView.vue'
import posts from '@/routes/posts';
import shop from '@/routes/shop';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]

defineProps<{
    weather?: WeatherData
}>()
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                
                <!-- Weather card -->
                <div
                    v-if="weather"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4"
                >
                    <h2 class="text-2xl font-bold">
                        {{ weather?.main?.temp }} °C
                    </h2>

                    <p class="text-lg tracking-wide capitalize opacity-90">
                        {{ weather?.weather?.[0]?.description }}
                    </p>

                    <p class="text-sm opacity-80">
                        {{ weather?.name }}, {{ weather?.sys?.country }}
                    </p>

                    <div class="mt-3 flex gap-5 text-sm opacity-90">
                        <span>💨 {{ weather?.wind?.speed }} m/s</span>
                        <span>💧 {{ weather?.main?.humidity }}%</span>
                    </div>

                    <img
                        v-if="weather?.weather?.[0]?.icon"
                        class="size-20 absolute top-0 right-0"
                        :src="`https://openweathermap.org/img/wn/${weather.weather[0].icon}@2x.png`"
                        alt="Weather icon"
                    />
                </div>

                <!-- Kui weather puudub -->
                <div
                    v-else
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center"
                >
                    <p class="opacity-70">Loading weather...</p>
                </div>

                <!-- Blog Link Card -->
                <Link
                    :href="posts.index().url"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center hover:bg-sidebar-accent transition-colors group"
                >
                    <div class="flex flex-col items-center gap-2">
                        <BookOpen class="size-8 text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors" />
                        <span class="text-sm font-medium text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors">Blog</span>
                    </div>
                </Link>

                <!-- Shop Link Card -->
                <Link
                    :href="shop.index().url"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center hover:bg-sidebar-accent transition-colors group"
                >
                    <div class="flex flex-col items-center gap-2">
                        <ShoppingCart class="size-8 text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors" />
                        <span class="text-sm font-medium text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors">Shop</span>
                    </div>
                </Link>
            </div>

            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <Mapview />
            </div>
        </div>
    </AppLayout>
</template>