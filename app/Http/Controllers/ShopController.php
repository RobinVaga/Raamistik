<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::with('reviews')
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'image' => $product->image,
                    'stock_quantity' => $product->stock_quantity,
                    'sku' => $product->sku,
                    'average_rating' => round($product->average_rating, 1),
                    'review_count' => $product->review_count,
                ];
            });

        return Inertia::render('shop/Index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product)
    {
        $product->load('reviews.user');

        return Inertia::render('shop/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image,
                'stock_quantity' => $product->stock_quantity,
                'sku' => $product->sku,
                'average_rating' => round($product->average_rating, 1),
                'review_count' => $product->review_count,
                'reviews' => $product->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'customer_name' => $review->customer_name ?? $review->user?->name ?? 'Anonymous',
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->format('M d, Y'),
                    ];
                }),
            ],
        ]);
    }
}