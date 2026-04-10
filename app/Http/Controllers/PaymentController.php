<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function stripe(Order $order)
    {
        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Check if order is already processed
        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'This order has already been processed.');
        }

        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Create or retrieve payment intent
            if ($order->stripe_payment_intent_id) {
                // Retrieve existing payment intent
                $paymentIntent = PaymentIntent::retrieve($order->stripe_payment_intent_id);
            } else {
                // Create new payment intent
                $paymentIntent = PaymentIntent::create([
                    'amount' => round($order->total_amount * 100), // Convert to cents
                    'currency' => 'eur',
                    'automatic_payment_methods' => [
                        'enabled' => true,
                    ],
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_id' => auth()->id(),
                    ],
                ]);

                // Save payment intent ID to order
                $order->update([
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ]);
            }

            return Inertia::render('shop/Stripe', [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount, // Ensure it's a float
                ],
                'clientSecret' => $paymentIntent->client_secret,
                'stripePublicKey' => config('services.stripe.key'),
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return back()->with('error', 'Payment initialization failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function stripeSuccess(Request $request, Order $order)
{
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    $validated = $request->validate([
        'payment_intent' => 'required|string',
    ]);

    Stripe::setApiKey(config('services.stripe.secret'));

    try {
        $paymentIntent = PaymentIntent::retrieve($validated['payment_intent']);

        if ($paymentIntent->status === 'succeeded') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);

            // Clear cart
            auth()->user()->cartItems()->delete();

            // Load order relationships for the success page
            $order->load('items.product');

            // Redirect to the new SuccessfulPayment view instead of orders.show
            return Inertia::render('shop/Succesful_payment', [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'first_name' => $order->first_name,
                    'last_name' => $order->last_name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                    'address' => $order->address ?? '',
                    'city' => $order->city ?? '',
                    'postal_code' => $order->postal_code ?? '',
                    'country' => $order->country ?? '',
                    'total_amount' => $order->total_amount,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at->format('Y-m-d H:i'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name,
                            'product_price' => $item->product_price,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'image' => $item->product->image,
                            ] : null,
                        ];
                    }),
                ],
            ]);
        }

        return back()->with('error', 'Payment was not successful.');

    } catch (\Exception $e) {
        Log::error('Payment confirmation error: ' . $e->getMessage());
        return back()->with('error', 'Payment confirmation failed: ' . $e->getMessage());
    }
}

    // Only include webhook method if you have webhook secret configured
    public function stripeWebhook(Request $request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret');

        // If no webhook secret is configured, return early
        if (!$endpoint_secret) {
            return response()->json(['error' => 'Webhook not configured'], 400);
        }

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSuccess($paymentIntent);
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handlePaymentFailure($paymentIntent);
                break;

            default:
                Log::info('Unhandled Stripe event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentSuccess($paymentIntent)
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);

            Log::info('Payment successful for order: ' . $order->order_number);
        }
    }

    private function handlePaymentFailure($paymentIntent)
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update([
                'payment_status' => 'failed',
            ]);

            Log::warning('Payment failed for order: ' . $order->order_number);
        }
    }
}