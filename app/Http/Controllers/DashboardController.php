<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $city = $request->input('city', 'Kuressaare');
        $apiKey = config('services.weather.key');
        
        $weather = null;
        
        try {
            $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                $weather = $response->json();
                Log::info('Weather API Success', [
                    'city' => $city,
                    'returned_city' => $weather['name'] ?? 'unknown'
                ]);
            } else {
                Log::error('Weather API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'city' => $city
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Weather API Exception', [
                'message' => $e->getMessage(),
                'city' => $city
            ]);
        }

        return Inertia::render('Dashboard', [
            'weather' => $weather,
        ]);
    }
}