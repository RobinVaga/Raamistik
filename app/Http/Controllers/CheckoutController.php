<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->cartItems()->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return Inertia::render('shop/Checkout', [
            'cartItems' => $cartItems,
            'total' => $total,
            'user' => auth()->user(),
            'stripePublicKey' => config('services.stripe.key'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'payment_method' => 'required|string|in:stripe,paypal',
        ]);

        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty!');
        }

        // Check stock
        foreach ($cartItems as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Product '{$item->product->name}' is not available in sufficient quantity.");
            }
        }

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'total_amount' => $total,
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->quantity * $item->product->price,
                ]);

                // Decrease stock
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            // Clear cart
            auth()->user()->cartItems()->delete();

            DB::commit();

            // Redirect to payment
            if ($validated['payment_method'] === 'stripe') {
                return redirect()->route('payment.stripe', ['order' => $order->id]);
            } else {
                return redirect()->route('payment.paypal', ['order' => $order->id]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Order creation failed: ' . $e->getMessage()]);
        }
    }
}