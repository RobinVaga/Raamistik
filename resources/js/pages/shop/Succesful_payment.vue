<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { CheckCircle, Package, MapPin, Mail, Phone, User, Calendar, CreditCard } from 'lucide-vue-next'

interface OrderItem {
    id: number
    product_name: string
    product_price: number
    quantity: number
    subtotal: number
    product: {
        id: number
        image: string
    } | null
}

interface Order {
    id: number
    order_number: string
    first_name: string
    last_name: string
    email: string
    phone: string
    address: string
    city: string
    postal_code: string
    country: string
    total_amount: number
    payment_status: string
    payment_method: string
    created_at: string
    items: OrderItem[]
}

const props = defineProps<{
    order: Order
}>()

const breadcrumbs = [
    { label: 'Shop', href: '/shop' },
    { label: 'Cart', href: '/cart' },
    { label: 'Checkout', href: '/checkout' },
    { label: 'Payment Successful', href: '#' },
]

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(price)
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <Head title="Payment Successful" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <CheckCircle class="h-12 w-12 text-green-600 dark:text-green-400" />
                    </div>
                    <h1 class="mb-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        Payment Successful!
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        Thank you for your order. We've received your payment and will process your order shortly.
                    </p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">
                        Order Number: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ order.order_number }}</span>
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- Order Summary -->
                    <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <Package class="h-5 w-5" />
                                Order Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 border-b border-gray-200 pb-4 last:border-0 last:pb-0 dark:border-gray-700">
                                    <img
                                        v-if="item.product?.image"
                                        :src="item.product.image"
                                        :alt="item.product_name"
                                        class="h-16 w-16 rounded-lg object-cover"
                                    />
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ item.product_name }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Quantity: {{ item.quantity }} × {{ formatPrice(item.product_price) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ formatPrice(item.subtotal) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">Total</span>
                                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatPrice(order.total_amount) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Customer Information -->
                    <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <User class="h-5 w-5" />
                                Customer Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Full Name</p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ order.first_name }} {{ order.last_name }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                        <Mail class="h-4 w-4" />
                                        Email
                                    </p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ order.email }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                        <Phone class="h-4 w-4" />
                                        Phone
                                    </p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ order.phone }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                        <Calendar class="h-4 w-4" />
                                        Order Date
                                    </p>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Shipping Address -->
                    <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
    <CardHeader>
        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
            <MapPin class="h-5 w-5" />
            Shipping Address
        </CardTitle>
    </CardHeader>
    <CardContent>
        <div v-if="order.address || order.city || order.postal_code || order.country" class="space-y-1">
            <p v-if="order.address" class="font-medium text-gray-900 dark:text-gray-100">
                {{ order.address }}
            </p>
            <p v-if="order.city || order.postal_code" class="text-gray-600 dark:text-gray-400">
                <template v-if="order.city">{{ order.city }}</template><template v-if="order.city && order.postal_code">, </template><template v-if="order.postal_code">{{ order.postal_code }}</template>
            </p>
            <p v-if="order.country" class="text-gray-600 dark:text-gray-400">
                {{ order.country }}
            </p>
        </div>
        <div v-else class="text-gray-500 dark:text-gray-400">
            <p>No shipping address provided</p>
        </div>
    </CardContent>
</Card>

                    <!-- Payment Information -->
                    <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <CreditCard class="h-5 w-5" />
                                Payment Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                                    <p class="font-medium capitalize text-gray-900 dark:text-gray-100">
                                        {{ order.payment_method }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Payment Status</p>
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        {{ order.payment_status }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                        <Button
                            as="a"
                            href="/shop"
                            variant="outline"
                            class="w-full sm:w-auto"
                        >
                            Continue Shopping
                        </Button>
                    </div>

                    <!-- Additional Information -->
                    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>What's next?</strong> We've sent a confirmation email to {{ order.email }}. 
                            You can track your order status in your account dashboard. If you have any questions, 
                            please don't hesitate to contact our support team.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>