<script setup lang="ts">
import { onMounted, ref, nextTick } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Loader2, CreditCard, AlertCircle, CheckCircle } from 'lucide-vue-next'
import { loadStripe, Stripe, StripeElements } from '@stripe/stripe-js'

interface Order {
    id: number
    order_number: string
    total_amount: number
    status: string
}

const props = defineProps<{
    order: Order
    clientSecret: string
    stripePublicKey: string
}>()

const stripe = ref<Stripe | null>(null)
const elements = ref<StripeElements | null>(null)
const isProcessing = ref(false)
const errorMessage = ref('')
const isLoading = ref(true)
const isElementReady = ref(false)

onMounted(async () => {
    try {
        // Load Stripe
        stripe.value = await loadStripe(props.stripePublicKey)
        
        if (!stripe.value) {
            throw new Error('Failed to load Stripe')
        }

        // Create elements
        elements.value = stripe.value.elements({
            clientSecret: props.clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#2563eb',
                    colorBackground: '#ffffff',
                    colorText: '#1f2937',
                    colorDanger: '#ef4444',
                    fontFamily: 'system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '8px',
                },
            },
        })

        // Wait for next tick to ensure DOM is ready
        await nextTick()

        // Verify the element exists
        const paymentElementContainer = document.querySelector('#payment-element')
        if (!paymentElementContainer) {
            throw new Error('Payment element container not found in DOM')
        }

        // Create payment element
        const paymentElement = elements.value.create('payment', {
            layout: {
                type: 'tabs',
                defaultCollapsed: false,
            },
        })
        
        // Add event listeners BEFORE mounting
        paymentElement.on('ready', () => {
            console.log('Payment element is ready')
            isElementReady.value = true
            isLoading.value = false
        })

        paymentElement.on('change', (event) => {
            if (event.error) {
                errorMessage.value = event.error.message
            } else {
                errorMessage.value = ''
            }
        })

        // Now mount the element
        paymentElement.mount('#payment-element')
        
    } catch (error) {
        console.error('Stripe initialization error:', error)
        errorMessage.value = 'Failed to initialize payment form. Please try again.'
        isLoading.value = false
    }
})

const handleSubmit = async () => {
    if (!stripe.value || !elements.value || isProcessing.value || !isElementReady.value) {
        if (!isElementReady.value) {
            errorMessage.value = 'Payment form is still loading. Please wait a moment.'
        }
        return
    }

    isProcessing.value = true
    errorMessage.value = ''

    try {
        const { error, paymentIntent } = await stripe.value.confirmPayment({
            elements: elements.value,
            confirmParams: {
                return_url: `${window.location.origin}/payment/stripe/${props.order.id}/success`,
            },
            redirect: 'if_required',
        })

        if (error) {
            errorMessage.value = error.message || 'Payment failed. Please try again.'
            isProcessing.value = false
        } else if (paymentIntent && paymentIntent.status === 'succeeded') {
            // Payment succeeded, redirect to success page
            router.post(`/payment/stripe/${props.order.id}/success`, {
                payment_intent: paymentIntent.id,
            })
        }
    } catch (error) {
        console.error('Payment error:', error)
        errorMessage.value = 'An unexpected error occurred. Please try again.'
        isProcessing.value = false
    }
}
</script>

<template>
    <Head title="Payment" />

    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <Card class="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <CreditCard class="w-5 h-5" />
                            Complete Your Payment
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Order Summary -->
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Order Number</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ order.order_number }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Amount</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        €{{ order.total_amount.toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="flex items-start gap-3 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">Payment Error</p>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ errorMessage }}</p>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <!-- Payment Element Container -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Payment Details
                                </label>
                                
                                <!-- Loading State -->
                                <div v-if="isLoading" class="flex flex-col items-center justify-center py-12 space-y-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800">
                                    <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Loading payment form...</p>
                                </div>

                                <!-- Payment Element -->
                                <div 
                                    id="payment-element" 
                                    v-show="!isLoading"
                                    class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800"
                                ></div>
                            </div>

                            <!-- Submit Button -->
                            <Button
                                type="submit"
                                :disabled="isProcessing || !isElementReady || isLoading"
                                class="w-full flex items-center justify-center gap-2 h-12 text-base"
                            >
                                <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin" />
                                <CreditCard v-else class="w-5 h-5" />
                                <span v-if="isProcessing">Processing Payment...</span>
                                <span v-else-if="!isElementReady || isLoading">Loading...</span>
                                <span v-else>Pay €{{ order.total_amount.toFixed(2) }}</span>
                            </Button>

                            <!-- Security Notice -->
                            <div class="flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <CheckCircle class="w-4 h-4" />
                                <p>Your payment is secured by Stripe. We never store your card details.</p>
                            </div>

                            <!-- Test Card Info (only for development) -->
                            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <p class="text-xs font-medium text-blue-800 dark:text-blue-200 mb-2">Test Mode - Use these test cards:</p>
                                <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                                    <li>• Success: 4242 4242 4242 4242</li>
                                    <li>• Requires authentication: 4000 0025 0000 3155</li>
                                    <li>• Declined: 4000 0000 0000 9995</li>
                                    <li>• Use any future expiry date and any 3-digit CVC</li>
                                </ul>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>