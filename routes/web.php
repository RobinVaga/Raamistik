<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MarkerController;
use App\Mail\Timetable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Cache;



/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Google OAuth routes (MUST be outside auth middleware)
|--------------------------------------------------------------------------
*/

Route::get('/auth/redirect', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);


/*
|--------------------------------------------------------------------------
| Protected routes (login required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
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

            return null;
        } catch (\Exception $e) {
            \Log::error('Weather API Error: ' . $e->getMessage());
            return null;
        }
    });

    return Inertia::render('Dashboard', [
        'weather' => $weather
    ]);
})->name('dashboard');

Route::get('/map', [MarkerController::class, 'index'])->name('map.index');
Route::get('/api/markers', [MarkerController::class, 'getMarkers'])->name('markers.api');


    /*
    |--------------------------------------------------------------------------
    | Posts
    |--------------------------------------------------------------------------
    */

    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])
        ->name('posts.edit');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('posts.destroy');


    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

    Route::post('/add-comment/{post}', [CommentController::class, 'store'])
        ->name('comments.add');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

         /*
    |--------------------------------------------------------------------------
    | Markers
    |--------------------------------------------------------------------------
    */

    Route::post('/markers', [MarkerController::class, 'store'])
        ->name('markers.store');

    Route::put('/markers/{marker}', [MarkerController::class, 'update'])
        ->name('markers.update');

    Route::delete('/markers/{marker}', [MarkerController::class, 'destroy'])
        ->name('markers.destroy');
});


/*
|--------------------------------------------------------------------------
| Test mail route
|--------------------------------------------------------------------------
*/

Route::get('/mailable', function () {

    $startDate = now()->startOfWeek();
    $endDate = now()->endOfWeek();

    $response = Http::get(
        'https://tahveltp.edu.ee/hois_back/timetableevents/timetableSearch',
        [
            'from' => $startDate->toIsoString(),
            'thru' => $endDate->toIsoString(),
            'lang' => 'ET',
            'page' => 0,
            'schoolId' => 38,
            'size' => 50,
            'studentGroups' => '39248890-7051-4182-9a9a-8a82259b862b',
        ]
    );

    $timetableEvents = collect($response['content'] ?? [])
        ->sortBy(fn ($event) => $event['date'] . ' ' . $event['timeStart'])
        ->groupBy(fn ($event) =>
            Carbon::parse($event['date'])
                ->locale('et_EE')
                ->dayName
        );

    return new Timetable($timetableEvents, $startDate, $endDate);
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