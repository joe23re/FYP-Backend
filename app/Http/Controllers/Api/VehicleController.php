<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'color' => ['nullable', 'string'],
            'year' => ['required'],
            'plate_number' => ['required', 'string'],
            'vin' => ['required', 'string'],
            'engine_type' => ['required', 'string'],
            'current_mileage' => ['required'],
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $request->user()->id,
            'brand' => $fields['brand'],
            'model' => $fields['model'],
            'color' => $fields['color'] ?? null,
            'year' => $fields['year'],
            'plate_number' => $fields['plate_number'],
            'vin' => $fields['vin'],
            'engine_type' => $fields['engine_type'],
            'current_mileage' => $fields['current_mileage'],
        ]);

        return response()->json([
            'message' => 'Vehicle added successfully',
            'vehicle' => $vehicle,
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $vehicle = Vehicle::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (! $vehicle) {
            return response()->json([
                'message' => 'Vehicle not found.',
            ], 404);
        }

        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
        ]);
    }
}