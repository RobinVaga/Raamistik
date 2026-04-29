<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Film, Search, SlidersHorizontal, Star } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet'
import { Card, CardContent } from '@/components/ui/card'
import type { BreadcrumbItem } from '@/types'

interface Movie {
    id: number
    title: string
    overview: string
    poster_path: string | null
    backdrop_path: string | null
    release_date: string | null
    vote_average: number
    vote_count: number
    popularity: number
    genre_ids: number[]
}

interface Genre {
    id: number
    name: string
}

interface Filters {
    search: string | null
    sort_by: string
    genre: number | null
    year: number | null
    limit: number
    page: number
}

interface Props {
    movies: {
        results: Movie[]
        total_results: number
        total_pages: number
        page: number
    }
    genres: Genre[]
    filters: Filters
}

const props = defineProps<Props>()

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Movies', href: '/movies' },
])

const searchQuery = ref(props.filters.search || '')
const selectedSortBy = ref(props.filters.sort_by)
const selectedGenre = ref(props.filters.genre?.toString() || '')
const selectedYear = ref(props.filters.year?.toString() || '')
const selectedLimit = ref(props.filters.limit.toString())

const sortOptions = [
    { value: 'popularity.desc', label: 'Popularity (High to Low)' },
    { value: 'popularity.asc', label: 'Popularity (Low to High)' },
    { value: 'release_date.desc', label: 'Release Date (Newest)' },
    { value: 'release_date.asc', label: 'Release Date (Oldest)' },
    { value: 'vote_average.desc', label: 'Rating (High to Low)' },
    { value: 'vote_average.asc', label: 'Rating (Low to High)' },
]

const limitOptions = [
    { value: '10', label: '10 results' },
    { value: '20', label: '20 results' },
    { value: '50', label: '50 results' },
    { value: '100', label: '100 results' },
]

const currentYear = new Date().getFullYear()
const yearOptions = Array.from({ length: 50 }, (_, i) => ({
    value: (currentYear - i).toString(),
    label: (currentYear - i).toString(),
}))

const getGenreName = (genreId: number): string => {
    const genre = props.genres.find(g => g.id === genreId)
    return genre ? genre.name : ''
}

const formatReleaseDate = (date: string | null): string => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const applyFilters = () => {
    const params: Record<string, any> = {
        sort_by: selectedSortBy.value,
        limit: selectedLimit.value,
        page: 1,
    }

    if (searchQuery.value) {
        params.search = searchQuery.value
    }

    if (selectedGenre.value) {
        params.genre = selectedGenre.value
    }

    if (selectedYear.value) {
        params.year = selectedYear.value
    }

    router.get('/movies', params, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedSortBy.value = 'popularity.desc'
    selectedGenre.value = ''
    selectedYear.value = ''
    selectedLimit.value = '20'

    router.get('/movies', {
        sort_by: 'popularity.desc',
        limit: 20,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const handleSearch = () => {
    applyFilters()
}

const goToPage = (page: number) => {
    const params: Record<string, any> = {
        ...props.filters,
        page,
    }

    router.get('/movies', params, {
        preserveState: true,
        preserveScroll: false,
    })
}

const hasActiveFilters = computed(() => {
    return !!(
        props.filters.search ||
        props.filters.genre ||
        props.filters.year ||
        props.filters.sort_by !== 'popularity.desc' ||
        props.filters.limit !== 20
    )
})

// Helper function for pagination - moved inside setup
const getPageNumbers = (): (number | string)[] => {
    const currentPage = props.movies.page
    const totalPages = props.movies.total_pages
    const delta = 2 // Number of pages to show on each side of current page
    const pages: (number | string)[] = []

    // Always show first page
    pages.push(1)

    // Calculate range around current page
    const rangeStart = Math.max(2, currentPage - delta)
    const rangeEnd = Math.min(totalPages - 1, currentPage + delta)

    // Add ellipsis after first page if needed
    if (rangeStart > 2) {
        pages.push('...')
    }

    // Add pages in range
    for (let i = rangeStart; i <= rangeEnd; i++) {
        pages.push(i)
    }

    // Add ellipsis before last page if needed
    if (rangeEnd < totalPages - 1) {
        pages.push('...')
    }

    // Always show last page if there's more than one page
    if (totalPages > 1) {
        pages.push(totalPages)
    }

    return pages
}
</script>

<template>
    <Head title="Movies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Movies</h1>
                    <p class="text-muted-foreground">
                        Browse and discover movies from The Movie Database
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Badge variant="secondary" class="text-sm">
                        {{ props.movies.total_results.toLocaleString() }} movies
                    </Badge>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="flex flex-col gap-4 md:flex-row">
                <!-- Search Bar -->
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search movies..."
                        class="pl-9"
                        @keyup.enter="handleSearch"
                    />
                </div>

                <!-- Filter Button (Mobile) -->
                <Sheet>
                    <SheetTrigger as-child>
                        <Button variant="outline" class="md:hidden">
                            <SlidersHorizontal class="mr-2 h-4 w-4" />
                            Filters
                            <Badge v-if="hasActiveFilters" variant="destructive" class="ml-2">
                                Active
                            </Badge>
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[300px] overflow-y-auto">
                        <SheetHeader>
                            <SheetTitle>Filter Movies</SheetTitle>
                            <SheetDescription>
                                Refine your movie search with filters
                            </SheetDescription>
                        </SheetHeader>

                        <div class="mt-6 space-y-4">
                            <!-- Sort By -->
                            <div class="space-y-2">
                                <Label>Sort By</Label>
                                <Select v-model="selectedSortBy">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select sort order" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in sortOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Genre -->
                            <div class="space-y-2">
                                <Label>Genre</Label>
                                <Select v-model="selectedGenre">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All genres" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All genres</SelectItem>
                                        <SelectItem
                                            v-for="genre in props.genres"
                                            :key="genre.id"
                                            :value="genre.id.toString()"
                                        >
                                            {{ genre.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Year -->
                            <div class="space-y-2">
                                <Label>Release Year</Label>
                                <Select v-model="selectedYear">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All years" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All years</SelectItem>
                                        <SelectItem
                                            v-for="year in yearOptions"
                                            :key="year.value"
                                            :value="year.value"
                                        >
                                            {{ year.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Results Limit -->
                            <div class="space-y-2">
                                <Label>Results per page</Label>
                                <Select v-model="selectedLimit">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in limitOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 pt-4">
                                <Button @click="applyFilters" class="flex-1">
                                    Apply Filters
                                </Button>
                                <Button @click="clearFilters" variant="outline">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>

                <!-- Desktop Filters -->
                <div class="hidden items-center gap-2 md:flex">
                    <Select v-model="selectedSortBy" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in sortOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="selectedGenre" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="Genre" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All genres</SelectItem>
                            <SelectItem
                                v-for="genre in props.genres"
                                :key="genre.id"
                                :value="genre.id.toString()"
                            >
                                {{ genre.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Button @click="handleSearch">
                        <Search class="mr-2 h-4 w-4" />
                        Search
                    </Button>

                    <Button v-if="hasActiveFilters" @click="clearFilters" variant="outline">
                        Clear Filters
                    </Button>
                </div>
            </div>

            <!-- Movies Grid -->
            <div v-if="props.movies.results.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link
                    v-for="movie in props.movies.results"
                    :key="movie.id"
                    :href="`/movies/${movie.id}`"
                    class="group"
                >
                    <Card class="overflow-hidden transition-all hover:shadow-lg">
                        <div class="relative aspect-[2/3] overflow-hidden bg-muted">
                            <img
                                v-if="movie.poster_path"
                                :src="`https://image.tmdb.org/t/p/w500${movie.poster_path}`"
                                :alt="movie.title"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-muted"
                            >
                                <Film class="h-16 w-16 text-muted-foreground" />
                            </div>

                            <!-- Rating Badge -->
                            <div class="absolute right-2 top-2">
                                <Badge
                                    class="flex items-center gap-1 bg-black/70 text-white backdrop-blur-sm"
                                >
                                    <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                    {{ movie.vote_average.toFixed(1) }}
                                </Badge>
                            </div>
                        </div>

                        <CardContent class="p-4">
                            <h3 class="line-clamp-1 font-semibold group-hover:text-primary">
                                {{ movie.title }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ formatReleaseDate(movie.release_date) }}
                            </p>

                            <!-- Genres -->
                            <div v-if="movie.genre_ids.length > 0" class="mt-2 flex flex-wrap gap-1">
                                <Badge
                                    v-for="genreId in movie.genre_ids.slice(0, 2)"
                                    :key="genreId"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    {{ getGenreName(genreId) }}
                                </Badge>
                            </div>

                            <!-- Overview -->
                            <p class="mt-2 line-clamp-2 text-sm text-muted-foreground">
                                {{ movie.overview }}
                            </p>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center"
            >
                <Film class="mb-4 h-12 w-12 text-muted-foreground" />
                <h3 class="mb-2 text-lg font-semibold">No movies found</h3>
                <p class="mb-4 text-sm text-muted-foreground">
                    Try adjusting your search or filters to find what you're looking for.
                </p>
                <Button @click="clearFilters" variant="outline">
                    Clear Filters
                </Button>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.movies.results.length > 0 && props.movies.total_pages > 1"
                class="flex items-center justify-between border-t pt-4"
            >
                <div class="text-sm text-muted-foreground">
                    Page {{ props.movies.page }} of {{ props.movies.total_pages }}
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        @click="goToPage(props.movies.page - 1)"
                        :disabled="props.movies.page === 1"
                        variant="outline"
                        size="sm"
                    >
                        Previous
                    </Button>

                    <!-- Page Numbers -->
                    <div class="hidden items-center gap-1 sm:flex">
                        <template v-for="pageNum in getPageNumbers()" :key="pageNum">
                            <Button
                                v-if="pageNum !== '...'"
                                @click="goToPage(Number(pageNum))"
                                :variant="pageNum === props.movies.page ? 'default' : 'outline'"
                                size="sm"
                                class="w-10"
                            >
                                {{ pageNum }}
                            </Button>
                            <span v-else class="px-2 text-muted-foreground">...</span>
                        </template>
                    </div>

                    <Button
                        @click="goToPage(props.movies.page + 1)"
                        :disabled="props.movies.page === props.movies.total_pages"
                        variant="outline"
                        size="sm"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>