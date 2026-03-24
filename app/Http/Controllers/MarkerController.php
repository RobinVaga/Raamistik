<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarkerController extends Controller
{
    public function index()
    {
        $markers = Marker::orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Map/Index', [
            'markers' => $markers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
        ]);

        Marker::create($validated);

        return redirect()->back()->with('success', 'Marker lisatud!');
    }

    public function update(Request $request, Marker $marker)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
        ]);

        $marker->update($validated);

        return redirect()->back()->with('success', 'Marker uuendatud!');
    }

    public function destroy(Marker $marker)
    {
        $marker->delete();

        return redirect()->back()->with('success', 'Marker kustutatud!');
    }

     public function getMarkers()
    {
        return response()->json(Marker::all());
    }

}