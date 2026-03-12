<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Mail\Timetable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Inertia\Inertia;

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
| Protected routes (login required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {

        $weather = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'q' => 'Tallinn',
            'appid' => env('WEATHER_API_KEY'),
            'units' => 'metric'
        ])->json();

        return Inertia::render('Dashboard', [
            'weather' => $weather
        ]);

    })->name('dashboard');



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