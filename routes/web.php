<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Mail\Timetable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');


/*
|--------------------------------------------------------------------------
| Protected routes (login required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $weather = Cache::remember('weather_tallinn', 1800, function () {
            try {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => 'Kuressaare,EE',
                    'appid' => env('WEATHER_API_KEY'),
                    'units' => 'metric'
                ]);
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                return null;
            }
        });

        return Inertia::render('Dashboard', [
            'weather' => $weather
        ]);
    })->name('dashboard');
    
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class,'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    

    Route::post('/add-comment/{post}', [CommentController::class, 'store'])
    ->name('comments.add');

    Route::get('/map', [MarkerController::class, 'index'])->name('map.index');
    

    Route::get('/api/markers', [MarkerController::class, 'getMarkers'])->name('markers.api');
    

    Route::get('/markers/{marker}', [MarkerController::class, 'show'])->name('markers.show');
    Route::post('/markers', [MarkerController::class, 'store'])->name('markers.store');
    Route::put('/markers/{marker}', [MarkerController::class, 'update'])->name('markers.update');
    Route::delete('/markers/{marker}', [MarkerController::class, 'destroy'])->name('markers.destroy');
});


/*
|--------------------------------------------------------------------------
| Test mail route
|--------------------------------------------------------------------------
*/

Route::get('/mailable', function () {
    return new Timetable($timetableEvents, $startDate, $endDate);
});

// Shop routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

// Cart routes (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [PaymentController::class, 'process'])->name('checkout.process');
    
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Other route files
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
require __DIR__.'/posts.php';
require __DIR__.'/authors.php';
require __DIR__.'/auth.php';