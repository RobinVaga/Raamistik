<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function stripe(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'See tellimus on juba töödeldud.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $order->total_amount * 100, // cents
                'currency' => 'eur',
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            $order->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            return Inertia::render('payment/Stripe', [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                ],
                'clientSecret' => $paymentIntent->client_secret,
                'stripePublicKey' => config('services.stripe.key'),
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Makse loomine ebaõnnestus: ' . $e->getMessage());
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
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ]);

                // Tühjenda ostukorv
                auth()->user()->cartItems()->delete();

                return redirect()->route('orders.show', $order)
                    ->with('success', 'Makse õnnestus! Tellimus on kinnitatud.');
            }

            return back()->with('error', 'Makse ei õnnestunud.');

        } catch (\Exception $e) {
            return back()->with('error', 'Makse kinnitamine ebaõnnestus: ' . $e->getMessage());
        }
    }

    public function paypal(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('info', 'See tellimus on juba töödeldud.');
        }

        return Inertia::render('payment/Paypal', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
            ],
            'paypalClientId' => config('services.paypal.client_id'),
        ]);
    }

    public function paypalSuccess(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'orderID' => 'required|string',
        ]);

        // PayPal makse kinnitamine
        // Siin peaks olema PayPal API kutse, et kinnitada makse
        // Lihtsustatud versioon:

        $order->update([
            'payment_status' => 'paid',
        ]);

        // Tühjenda ostukorv
        auth()->user()->cartItems()->delete();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Makse õnnestus! Tellimus on kinnitatud.');
    }
}