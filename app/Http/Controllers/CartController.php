<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()
            ->cartItems()
            ->with('product')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                        'stock_quantity' => $item->product->stock_quantity,
                    ],
                ];
            });

        $total = $cartItems->sum('subtotal');

        return Inertia::render('shop/Cart', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Toode pole piisavas koguses laos.');
        }

        $cartItem = CartItem::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            
            if ($product->stock_quantity < $newQuantity) {
                return back()->with('error', 'Toode pole piisavas koguses laos.');
            }
            
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return back()->with('success', 'Toode lisatud ostukorvi!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cartItem->product->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Toode pole piisavas koguses laos.');
        }

        $cartItem->update($validated);

        return back()->with('success', 'Ostukorv uuendatud!');
    }

    public function destroy(CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Toode eemaldatud ostukorvist!');
    }

    public function clear()
    {
        auth()->user()->cartItems()->delete();

        return back()->with('success', 'Ostukorv tühjendatud!');
    }
}