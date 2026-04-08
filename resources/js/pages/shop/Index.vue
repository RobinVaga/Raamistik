<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ShoppingCart } from 'lucide-vue-next'

interface Product {
    id: number
    name: string
    description: string
    price: number
    image: string | null
    stock_quantity: number
    sku: string
    average_rating: number
    review_count: number
}

defineProps<{
    products: Product[]
    cartItemsCount?: number
}>()
</script>

<template>
    <Head title="Shop" />

    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-white">
                        Shop
                    </h2>
                    
                    <Link href="/cart">
                        <Button variant="outline" class="relative">
                            <ShoppingCart class="h-5 w-5" />
                            <span v-if="cartItemsCount && cartItemsCount > 0" 
                                  class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                                {{ cartItemsCount }}
                            </span>
                            <span class="ml-2">Cart</span>
                        </Button>
                    </Link>
                </div>

                <div class="overflow-hidden bg-black shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="product in products"
                                :key="product.id"
                                class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md"
                            >
                                <Link
                                    :href="`/shop/${product.id}`"
                                    class="block"
                                >
                                    <div
                                        class="aspect-square w-full overflow-hidden bg-gray-200"
                                    >
                                        <img
                                            v-if="product.image"
                                            :src="product.image"
                                            :alt="product.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full items-center justify-center text-gray-400"
                                        >
                                            No Image
                                        </div>
                                    </div>

                                    <div class="p-4">
                                        <h3
                                            class="mb-2 text-lg font-semibold text-gray-900"
                                        >
                                            {{ product.name }}
                                        </h3>

                                        <p
                                            class="mb-3 line-clamp-2 text-sm text-gray-600"
                                        >
                                            {{ product.description }}
                                        </p>

                                        <div
                                            class="mb-3 flex items-center gap-2"
                                        >
                                            <div class="flex items-center">
                                                <svg
                                                    v-for="star in 5"
                                                    :key="star"
                                                    class="h-4 w-4"
                                                    :class="
                                                        star <=
                                                        product.average_rating
                                                            ? 'text-yellow-400'
                                                            : 'text-gray-300'
                                                    "
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                                    />
                                                </svg>
                                            </div>
                                            <span class="text-sm text-gray-600">
                                                ({{ product.review_count }})
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="text-xl font-bold text-gray-900"
                                            >
                                                €{{ product.price }}
                                            </span>
                                            <span
                                                class="text-sm"
                                                :class="
                                                    product.stock_quantity > 0
                                                        ? 'text-green-600'
                                                        : 'text-red-600'
                                                "
                                            >
                                                {{
                                                    product.stock_quantity > 0
                                                        ? `${product.stock_quantity} in stock`
                                                        : 'Out of stock'
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <div
                            v-if="products.length === 0"
                            class="py-12 text-center"
                        >
                            <p class="text-gray-500">
                                No products available at the moment.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>