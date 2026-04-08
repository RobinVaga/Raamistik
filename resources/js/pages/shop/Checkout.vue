<script setup lang="ts">
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { 
    CreditCard, 
    Wallet, 
    MapPin, 
    User, 
    Mail, 
    Phone, 
    ShoppingCart,
    Loader2,
    AlertCircle
} from 'lucide-vue-next'

interface CartItem {
    id: number
    quantity: number
    subtotal: number
    product: {
        id: number
        name: string
        price: number
        image: string | null
    }
}

interface BreadcrumbItem {
    title: string
    href: string
}

interface User {
    name: string
    email: string
}

const props = defineProps<{
    cartItems: CartItem[]
    total: number
    user: User
    stripePublicKey?: string
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'cart', href: '/cart' },
    { title: 'Checkout', href: '/checkout' },
]


const isProcessing = ref(false)

// Split user name into first and last name
const [firstName, ...lastNameParts] = props.user.name.split(' ')
const lastName = lastNameParts.join(' ')

const form = useForm({
    first_name: firstName || '',
    last_name: lastName || '',
    email: props.user.email || '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country: 'Estonia',
    payment_method: 'stripe' as 'stripe' | 'paypal',
})

const isFormValid = computed(() => {
    return (
        form.first_name.trim() !== '' &&
        form.last_name.trim() !== '' &&
        form.email.trim() !== '' &&
        form.phone.trim() !== '' &&
        form.address.trim() !== '' &&
        form.city.trim() !== '' &&
        form.postal_code.trim() !== '' &&
        form.country.trim() !== '' &&
        form.payment_method !== 'stripe'
    )
})

const formatPrice = (price: number | string) => {
    const numPrice = typeof price === 'string' ? parseFloat(price) : price
    return `€${numPrice.toFixed(2)}`
}

const handleSubmit = () => {
    if (!isFormValid.value || isProcessing.value) {
        return
    }

    isProcessing.value = true

    const endpoint = form.payment_method === 'stripe' 
        ? '/checkout/stripe' 
        : '/checkout/paypal'

    form.post(endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            // Payment processing will redirect
        },
        onError: (errors) => {
            console.error('Checkout errors:', errors)
            isProcessing.value = false
        },
        onFinish: () => {
            // Don't set isProcessing to false here as we might be redirecting
        },
    })
}
</script>
<template>
    <Head title="Checkout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Checkout
                        </h2>

                        <!-- Error Alert -->
                        <div 
                            v-if="Object.keys(form.errors).length > 0" 
                            class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
                        >
                            <div class="flex items-start gap-3">
                                <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">
                                        Please fix the following errors:
                                    </h3>
                                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                                        <li v-for="(error, key) in form.errors" :key="key">
                                            {{ error }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <!-- Left Column - Forms -->
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Contact Information -->
                                <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <CardHeader>
                                        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                            <User class="w-5 h-5" />
                                            Contact Information
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="first_name" class="text-gray-700 dark:text-gray-300">
                                                    First Name *
                                                </Label>
                                                <Input
                                                    id="first_name"
                                                    v-model="form.first_name"
                                                    type="text"
                                                    placeholder="Enter your first name"
                                                    :class="{ 'border-red-500': form.errors.first_name }"
                                                    class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                    required
                                                />
                                                <p v-if="form.errors.first_name" class="text-sm text-red-500">
                                                    {{ form.errors.first_name }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="last_name" class="text-gray-700 dark:text-gray-300">
                                                    Last Name *
                                                </Label>
                                                <Input
                                                    id="last_name"
                                                    v-model="form.last_name"
                                                    type="text"
                                                    placeholder="Enter your last name"
                                                    :class="{ 'border-red-500': form.errors.last_name }"
                                                    class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                    required
                                                />
                                                <p v-if="form.errors.last_name" class="text-sm text-red-500">
                                                    {{ form.errors.last_name }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="email" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                                <Mail class="w-4 h-4" />
                                                Email *
                                            </Label>
                                            <Input
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                placeholder="your.email@example.com"
                                                :class="{ 'border-red-500': form.errors.email }"
                                                class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                required
                                            />
                                            <p v-if="form.errors.email" class="text-sm text-red-500">
                                                {{ form.errors.email }}
                                            </p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="phone" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                                <Phone class="w-4 h-4" />
                                                Phone *
                                            </Label>
                                            <Input
                                                id="phone"
                                                v-model="form.phone"
                                                type="tel"
                                                placeholder="+372 5XXX XXXX"
                                                :class="{ 'border-red-500': form.errors.phone }"
                                                class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                required
                                            />
                                            <p v-if="form.errors.phone" class="text-sm text-red-500">
                                                {{ form.errors.phone }}
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Shipping Address -->
                                <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <CardHeader>
                                        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                            <MapPin class="w-5 h-5" />
                                            Shipping Address
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div class="space-y-2">
                                            <Label for="address" class="text-gray-700 dark:text-gray-300">
                                                Street Address *
                                            </Label>
                                            <Input
                                                id="address"
                                                v-model="form.address"
                                                type="text"
                                                placeholder="Street name and number"
                                                :class="{ 'border-red-500': form.errors.address }"
                                                class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                required
                                            />
                                            <p v-if="form.errors.address" class="text-sm text-red-500">
                                                {{ form.errors.address }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="city" class="text-gray-700 dark:text-gray-300">
                                                    City *
                                                </Label>
                                                <Input
                                                    id="city"
                                                    v-model="form.city"
                                                    type="text"
                                                    placeholder="Enter city"
                                                    :class="{ 'border-red-500': form.errors.city }"
                                                    class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                    required
                                                />
                                                <p v-if="form.errors.city" class="text-sm text-red-500">
                                                    {{ form.errors.city }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="postal_code" class="text-gray-700 dark:text-gray-300">
                                                    Postal Code *
                                                </Label>
                                                <Input
                                                    id="postal_code"
                                                    v-model="form.postal_code"
                                                    type="text"
                                                    placeholder="12345"
                                                    :class="{ 'border-red-500': form.errors.postal_code }"
                                                    class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                    required
                                                />
                                                <p v-if="form.errors.postal_code" class="text-sm text-red-500">
                                                    {{ form.errors.postal_code }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="country" class="text-gray-700 dark:text-gray-300">
                                                Country *
                                            </Label>
                                            <Input
                                                id="country"
                                                v-model="form.country"
                                                type="text"
                                                placeholder="Estonia"
                                                :class="{ 'border-red-500': form.errors.country }"
                                                class="dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                                required
                                            />
                                            <p v-if="form.errors.country" class="text-sm text-red-500">
                                                {{ form.errors.country }}
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Payment Method -->
                                <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <CardHeader>
                                        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                            <CreditCard class="w-5 h-5" />
                                            Payment Method
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <RadioGroup v-model="form.payment_method" class="space-y-3">
                                            <div class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                <RadioGroupItem value="stripe" id="stripe" />
                                                <Label for="stripe" class="flex items-center gap-2 cursor-pointer flex-1 text-gray-700 dark:text-gray-300">
                                                    <CreditCard class="w-5 h-5" />
                                                    <span>Credit Card (Stripe)</span>
                                                </Label>
                                            </div>
                                            <div class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                <RadioGroupItem value="paypal" id="paypal" />
                                                <Label for="paypal" class="flex items-center gap-2 cursor-pointer flex-1 text-gray-700 dark:text-gray-300">
                                                    <Wallet class="w-5 h-5" />
                                                    <span>PayPal</span>
                                                </Label>
                                            </div>
                                        </RadioGroup>
                                        <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-500">
                                            {{ form.errors.payment_method }}
                                        </p>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Right Column - Order Summary -->
                            <div class="lg:col-span-1">
                                <Card class="sticky top-6 border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                                    <CardHeader>
                                        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                            <ShoppingCart class="w-5 h-5" />
                                            Order Summary
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div class="space-y-3">
                                            <div 
                                                v-for="item in cartItems" 
                                                :key="item.id"
                                                class="flex items-center gap-3 pb-3 border-b border-gray-200 dark:border-gray-700 last:border-0"
                                            >
                                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
                                                    <img
                                                        v-if="item.product.image"
                                                        :src="item.product.image"
                                                        :alt="item.product.name"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div
                                                        v-else
                                                        class="flex h-full items-center justify-center text-xs text-gray-400 dark:text-gray-500"
                                                    >
                                                        No Image
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ item.product.name }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        Qty: {{ item.quantity }}
                                                    </p>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ formatPrice(item.subtotal) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="space-y-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                                <span>Subtotal</span>
                                                <span>{{ formatPrice(total) }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                                <span>Shipping</span>
                                                <span>Calculated at next step</span>
                                            </div>
                                            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-100 pt-2 border-t border-gray-200 dark:border-gray-700">
                                                <span>Total</span>
                                                <span>{{ formatPrice(total) }}</span>
                                            </div>
                                        </div>

                                        <Button
                                            @click="handleSubmit"
                                            :disabled="!isFormValid || isProcessing"
                                            class="w-full"
                                        >
                                            <Loader2 v-if="isProcessing" class="w-4 h-4 mr-2 animate-spin" />
                                            <span v-if="isProcessing">Processing...</span>
                                            <span v-else>Place Order</span>
                                        </Button>

                                        <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                                            By placing your order, you agree to our terms and conditions
                                        </p>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
