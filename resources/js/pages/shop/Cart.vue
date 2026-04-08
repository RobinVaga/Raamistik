<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Minus, Plus, Trash2 } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'

interface CartItem {
    id: number
    quantity: number
    subtotal: number
    product: {
        id: number
        name: string
        price: number
        image: string | null
        stock_quantity: number
    }
}

const props = defineProps<{
    cartItems: CartItem[]
    total: number
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cart',
        href: '/cart',
    },
]

const updateQuantity = (itemId: number, newQuantity: number) => {
    if (newQuantity < 1) return
    
    router.patch(`/cart/${itemId}`, {
        quantity: newQuantity
    }, {
        preserveScroll: true,
        preserveState: true
    })
}

const removeItem = (itemId: number) => {
    if (confirm('Are you sure you want to remove this item?')) {
        router.delete(`/cart/${itemId}`, {
            preserveScroll: true
        })
    }
}

const proceedToCheckout = () => {
    router.visit('/checkout')
}
</script>

<template>
    <Head title="Shopping Cart" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Shopping Cart
                        </h2>

                        <div v-if="cartItems.length === 0" class="py-12 text-center">
                            <p class="mb-4 text-gray-500 dark:text-gray-400">Your cart is empty</p>
                            <Button @click="router.visit('/shop')">
                                Continue Shopping
                            </Button>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <div class="lg:col-span-2">
                                <Card 
                                    v-for="item in cartItems" 
                                    :key="item.id"
                                    class="mb-4 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <CardContent class="p-6">
                                        <div class="flex items-center gap-6">
                                            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
                                                <img
                                                    v-if="item.product.image"
                                                    :src="item.product.image"
                                                    :alt="item.product.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500"
                                                >
                                                    No Image
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ item.product.name }}
                                                </h3>
                                                <p class="text-gray-600 dark:text-gray-400">
                                                    €{{ Number(item.product.price).toFixed(2) }}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="updateQuantity(item.id, item.quantity - 1)"
                                                    :disabled="item.quantity <= 1"
                                                    class="dark:border-gray-600 dark:hover:bg-gray-800"
                                                >
                                                    <Minus class="h-4 w-4" />
                                                </Button>
                                                
                                                <span class="w-12 text-center font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ item.quantity }}
                                                </span>
                                                
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="updateQuantity(item.id, item.quantity + 1)"
                                                    :disabled="item.quantity >= item.product.stock_quantity"
                                                    class="dark:border-gray-600 dark:hover:bg-gray-800"
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </Button>
                                            </div>

                                            <div class="min-w-[100px] text-right">
                                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                                    €{{ Number(item.subtotal).toFixed(2) }}
                                                </p>
                                            </div>

                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                @click="removeItem(item.id)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <div class="lg:col-span-1">
                                <Card class="sticky top-6 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <CardHeader>
                                        <CardTitle class="text-gray-900 dark:text-gray-100">Order Summary</CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                            <span>Subtotal</span>
                                            <span>€{{ Number(total).toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                            <span>Shipping</span>
                                            <span>Calculated at checkout</span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100">
                                                <span>Total</span>
                                                <span>€{{ Number(total).toFixed(2) }}</span>
                                            </div>
                                        </div>
                                    </CardContent>
                                    <CardFooter class="flex flex-col gap-3">
                                        <Button
                                            @click="proceedToCheckout"
                                            class="w-full"
                                        >
                                            Proceed to Checkout
                                        </Button>
                                        <Button
                                            variant="outline"
                                            @click="router.visit('/shop')"
                                            class="w-full dark:border-gray-600 dark:hover:bg-gray-800"
                                        >
                                            Continue Shopping
                                        </Button>
                                    </CardFooter>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>