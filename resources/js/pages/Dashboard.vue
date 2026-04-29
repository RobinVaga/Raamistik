<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { WeatherData, type BreadcrumbItem } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { BookOpen, ShoppingCart, MapIcon, Film, Search } from 'lucide-vue-next'
import posts from '@/routes/posts';
import shop from '@/routes/shop';
import map from '@/routes/map';
import movies from '@/routes/movies';
import { ref, computed, watch } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]

const props = defineProps<{
    weather?: WeatherData
}>()

const page = usePage()

const searchCity = ref('')
const isSearching = ref(false)

// Debug: Watch props changes
watch(() => props.weather, (newWeather) => {
    console.log('Props weather changed:', JSON.parse(JSON.stringify(newWeather)))
}, { deep: true, immediate: true })

// Use computed instead of ref + watch for better reactivity
const currentWeather = computed(() => {
    const weather = props.weather || page.props.weather as WeatherData | undefined
    console.log('Current weather computed:', JSON.parse(JSON.stringify(weather || null)))
    return weather
})

const searchWeather = () => {
    if (searchCity.value.trim() && !isSearching.value) {
        isSearching.value = true
        console.log('Searching for city:', searchCity.value)
        
        router.get(
            dashboard().url,
            { city: searchCity.value },
            {
                preserveState: false,
                preserveScroll: false,
                onSuccess: (page) => {
                    console.log('Search success, page props:', JSON.parse(JSON.stringify(page.props)))
                },
                onError: (errors) => {
                    console.error('Search error:', errors)
                },
                onFinish: () => {
                    isSearching.value = false
                    console.log('Search finished')
                }
            }
        )
    }
}

const resetWeather = () => {
    if (!isSearching.value) {
        isSearching.value = true
        searchCity.value = ''
        console.log('Resetting weather to default')
        
        router.get(
            dashboard().url,
            {},
            {
                preserveState: false,
                preserveScroll: false,
                onSuccess: (page) => {
                    console.log('Reset success, page props:', JSON.parse(JSON.stringify(page.props)))
                },
                onFinish: () => {
                    isSearching.value = false
                    console.log('Reset finished')
                }
            }
        )
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                
                <!-- Weather card -->
                <div
                    v-if="currentWeather"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col"
                >
                    <!-- Search Bar -->
                    <form @submit.prevent="searchWeather" class="mb-3 flex gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-2 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                            <input
                                v-model="searchCity"
                                type="text"
                                placeholder="Search city..."
                                :disabled="isSearching"
                                class="w-full pl-8 pr-2 py-1.5 text-sm rounded-md border border-sidebar-border/70 bg-background focus:outline-none focus:ring-2 focus:ring-primary/50 disabled:opacity-50 disabled:cursor-not-allowed"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="isSearching || !searchCity.trim()"
                            class="px-3 py-1.5 text-xs rounded-md border border-sidebar-border/70 hover:bg-sidebar-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ isSearching ? 'Loading...' : 'Search' }}
                        </button>
                        <button
                            type="button"
                            @click="resetWeather"
                            :disabled="isSearching"
                            class="px-2 py-1.5 text-xs rounded-md border border-sidebar-border/70 hover:bg-sidebar-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Reset
                        </button>
                    </form>

                    <div class="flex-1">
                        <h2 class="text-2xl font-bold">
                            {{ currentWeather?.main?.temp?.toFixed(1) }} °C
                        </h2>

                        <p class="text-lg tracking-wide capitalize opacity-90">
                            {{ currentWeather?.weather?.[0]?.description }}
                        </p>

                        <p class="text-sm opacity-80">
                            {{ currentWeather?.name }}, {{ currentWeather?.sys?.country }}
                        </p>

                        <div class="mt-3 flex gap-5 text-sm opacity-90">
                            <span>💨 {{ currentWeather?.wind?.speed }} m/s</span>
                            <span>💧 {{ currentWeather?.main?.humidity }}%</span>
                        </div>
                    </div>

                    <img
                        v-if="currentWeather?.weather?.[0]?.icon"
                        class="size-20 absolute top-12 right-0"
                        :src="`https://openweathermap.org/img/wn/${currentWeather.weather[0].icon}@2x.png`"
                        alt="Weather icon"
                    />
                </div>

                <!-- Kui weather puudub -->
                <div
                    v-else
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center"
                >
                    <p class="opacity-70">{{ isSearching ? 'Searching...' : 'Loading weather...' }}</p>
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
                <Link
                    :href="map.index().url"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center hover:bg-sidebar-accent transition-colors group"
                >
                    <div class="flex flex-col items-center gap-2">
                        <MapIcon class="size-8 text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors" />
                        <span class="text-sm font-medium text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors">Map</span>
                    </div>
                </Link>

                <Link
                    :href="movies.index().url"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center hover:bg-sidebar-accent transition-colors group"
                >
                    <div class="flex flex-col items-center gap-2">
                        <Film class="size-8 text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors" />
                        <span class="text-sm font-medium text-sidebar-foreground/60 group-hover:text-sidebar-foreground transition-colors">Movies</span>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>