<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import {
    Film,
    Star,
    Calendar,
    Clock,
    ArrowLeft,
    Play,
    TrendingUp,
    DollarSign,
    Users,
} from 'lucide-vue-next'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'

interface Genre {
    id: number
    name: string
}

interface ProductionCompany {
    id: number
    name: string
    logo_path: string | null
    origin_country: string
}

interface CastMember {
    id: number
    name: string
    character: string
    profile_path: string | null
    order: number
}

interface Director {
    id: number
    name: string
    job: string
    profile_path: string | null
}

interface Video {
    id: string
    key: string
    name: string
    site: string
    type: string
}

interface SimilarMovie {
    id: number
    title: string
    poster_path: string | null
    vote_average: number
}

interface Movie {
    id: number
    title: string
    tagline: string
    overview: string
    poster_path: string | null
    backdrop_path: string | null
    release_date: string | null
    runtime: number
    vote_average: number
    vote_count: number
    popularity: number
    budget: number
    revenue: number
    genres: Genre[]
    production_companies: ProductionCompany[]
    cast: CastMember[]
    director: Director | null
    videos: Video[]
    similar: SimilarMovie[]
}

const props = defineProps<{
    movie: Movie
}>()

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Movies', href: '/movies' },
    { title: props.movie.title },
])

const mainTrailer = computed(() => {
    return props.movie.videos?.find((video) => video.type === 'Trailer' && video.site === 'YouTube')
})

const ratingPercentage = computed(() => {
    return Math.round((props.movie.vote_average / 10) * 100)
})

const ratingColor = computed(() => {
    const percentage = ratingPercentage.value
    if (percentage >= 70) return 'text-green-600'
    if (percentage >= 50) return 'text-yellow-600'
    return 'text-red-600'
})

const formatReleaseDate = (date: string | null) => {
    if (!date) return 'Unknown'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

const formatRuntime = (minutes: number) => {
    if (!minutes) return 'Unknown'
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return `${hours}h ${mins}m`
}

const formatCurrency = (amount: number) => {
    if (!amount) return 'N/A'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount)
}

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
}
</script>

<template>
    <Head :title="movie.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col">
            <!-- Hero Section with Backdrop -->
            <div class="relative h-[400px] w-full overflow-hidden md:h-[500px]">
                <!-- Backdrop Image -->
                <div class="absolute inset-0">
                    <img
                        v-if="movie.backdrop_path"
                        :src="movie.backdrop_path"
                        :alt="movie.title"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-muted"
                    >
                        <Film class="h-32 w-32 text-muted-foreground" />
                    </div>
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-background via-background/80 to-transparent" />
                </div>

                <!-- Content Overlay -->
                <div class="relative z-10 flex h-full items-end p-6 md:p-10">
                    <div class="flex w-full flex-col gap-4 md:flex-row md:gap-8">
                        <!-- Poster -->
                        <div class="hidden md:block">
                            <Card class="w-[200px] overflow-hidden">
                                <img
                                    v-if="movie.poster_path"
                                    :src="movie.poster_path"
                                    :alt="movie.title"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex aspect-[2/3] w-full items-center justify-center bg-muted"
                                >
                                    <Film class="h-16 w-16 text-muted-foreground" />
                                </div>
                            </Card>
                        </div>

                        <!-- Movie Info -->
                        <div class="flex flex-1 flex-col justify-end gap-4">
                            <div>
                                <h1 class="text-3xl font-bold tracking-tight md:text-4xl">
                                    {{ movie.title }}
                                </h1>
                                <p v-if="movie.tagline" class="mt-2 text-lg italic text-muted-foreground">
                                    "{{ movie.tagline }}"
                                </p>
                            </div>

                            <!-- Quick Info -->
                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                    <span class="font-semibold">{{ movie.vote_average.toFixed(1) }}</span>
                                    <span class="text-muted-foreground">({{ movie.vote_count.toLocaleString() }} votes)</span>
                                </div>
                                <Separator orientation="vertical" class="h-4" />
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    <span>{{ formatReleaseDate(movie.release_date) }}</span>
                                </div>
                                <Separator orientation="vertical" class="h-4" />
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4" />
                                    <span>{{ formatRuntime(movie.runtime) }}</span>
                                </div>
                            </div>

                            <!-- Genres -->
                            <div v-if="movie.genres && movie.genres.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="genre in movie.genres"
                                    :key="genre.id"
                                    class="inline-flex items-center rounded-md bg-secondary px-2.5 py-0.5 text-xs font-semibold text-secondary-foreground"
                                >
                                    {{ genre.name }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <Button as-child>
                                    <Link href="/movies">
                                        <ArrowLeft class="mr-2 h-4 w-4" />
                                        Back to Movies
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6 p-6 md:p-10">
                <!-- Overview -->
                <Card>
                    <CardHeader>
                        <CardTitle>Overview</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="leading-relaxed text-muted-foreground">
                            {{ movie.overview || 'No overview available.' }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Stats Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Rating -->
                    <Card>
                        <CardContent class="flex items-center gap-4 p-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <Star class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">User Score</p>
                                <p :class="['text-2xl font-bold', ratingColor]">
                                    {{ ratingPercentage }}%
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Popularity -->
                    <Card>
                        <CardContent class="flex items-center gap-4 p-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <TrendingUp class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Popularity</p>
                                <p class="text-2xl font-bold">
                                    {{ Math.round(movie.popularity) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Budget -->
                    <Card>
                        <CardContent class="flex items-center gap-4 p-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <DollarSign class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Budget</p>
                                <p class="text-2xl font-bold">
                                    {{ formatCurrency(movie.budget) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Revenue -->
                    <Card>
                        <CardContent class="flex items-center gap-4 p-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <TrendingUp class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Revenue</p>
                                <p class="text-2xl font-bold">
                                    {{ formatCurrency(movie.revenue) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Cast Section -->
                <Card v-if="movie.cast && movie.cast.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5" />
                            Cast
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <div
                                v-for="member in movie.cast.slice(0, 8)"
                                :key="member.id"
                                class="flex items-center gap-3"
                            >
                                <Avatar>
                                    <AvatarImage
                                        v-if="member.profile_path"
                                        :src="member.profile_path"
                                        :alt="member.name"
                                    />
                                    <AvatarFallback>{{ getInitials(member.name) }}</AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">{{ member.name }}</p>
                                    <p class="truncate text-sm text-muted-foreground">
                                        {{ member.character }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Director Section -->
                <Card v-if="movie.director">
                    <CardHeader>
                        <CardTitle>Director</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-3">
                            <Avatar class="h-16 w-16">
                                <AvatarImage
                                    v-if="movie.director.profile_path"
                                    :src="movie.director.profile_path"
                                    :alt="movie.director.name"
                                />
                                <AvatarFallback>{{ getInitials(movie.director.name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="font-medium">{{ movie.director.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ movie.director.job }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Production Companies -->
                <Card v-if="movie.production_companies && movie.production_companies.length > 0">
                    <CardHeader>
                        <CardTitle>Production Companies</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <div
                                v-for="company in movie.production_companies"
                                :key="company.id"
                                class="flex items-center gap-3 rounded-lg border p-3"
                            >
                                <div
                                    v-if="company.logo_path"
                                    class="flex h-12 w-12 items-center justify-center"
                                >
                                    <img
                                        :src="company.logo_path"
                                        :alt="company.name"
                                        class="max-h-full max-w-full object-contain"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ company.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ company.origin_country }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Similar Movies -->
                <Card v-if="movie.similar && movie.similar.length > 0">
                    <CardHeader>
                        <CardTitle>Similar Movies</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <Link
                                v-for="similar in movie.similar.slice(0, 8)"
                                :key="similar.id"
                                :href="`/movies/${similar.id}`"
                                class="group overflow-hidden rounded-lg border transition-colors hover:border-primary"
                            >
                                <div class="aspect-[2/3] overflow-hidden bg-muted">
                                    <img
                                        v-if="similar.poster_path"
                                        :src="similar.poster_path"
                                        :alt="similar.title"
                                        class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center"
                                    >
                                        <Film class="h-16 w-16 text-muted-foreground" />
                                    </div>
                                </div>
                                <div class="p-3">
                                    <p class="truncate font-medium">{{ similar.title }}</p>
                                    <div class="mt-1 flex items-center gap-1">
                                        <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                        <span class="text-sm text-muted-foreground">
                                            {{ similar.vote_average.toFixed(1) }}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>