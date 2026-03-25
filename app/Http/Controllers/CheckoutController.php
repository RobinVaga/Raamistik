<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()
            ->cartItems()
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Ostukorv on tühi!');
        }

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return Inertia::render('checkout/Index', [
            'cartItems' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                    ],
                ];
            }),
            'total' => $total,
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
            'payment_method' => 'required|string|in:stripe,paypal',
        ]);

        $cartItems = auth()->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Ostukorv on tühi!');
        }

        // Kontrolli laoseisu
        foreach ($cartItems as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Toode '{$item->product->name}' pole piisavas koguses laos.");
            }
        }

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        try {
            DB::beginTransaction();

            // Loo tellimus
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'total_amount' => $total,
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
            ]);

            // Loo tellimuse read
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->quantity * $item->product->price,
                ]);

                // Vähenda laoseisu
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            DB::commit();

            // Suuna maksele
            if ($validated['payment_method'] === 'stripe') {
                return redirect()->route('payment.stripe', ['order' => $order->id]);
            } else {
                return redirect()->route('payment.paypal', ['order' => $order->id]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Tellimuse loomine ebaõnnestus: ' . $e->getMessage());
        }
    }
}