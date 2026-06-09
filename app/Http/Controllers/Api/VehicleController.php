<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'brand' => ['required', 'string', Rule::in(['Audi', 'Golf'])],
            'model' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'max:2026'],
            'plate_number' => [
                'required',
                'string',
                'regex:/^[BGZSNAY][0-9]{3,7}$/',
                'unique:vehicles,plate_number',
            ],
            'vin' => [
                'required',
                'string',
                'size:17',
                'regex:/^[A-HJ-NPR-Z0-9]{17}$/',
                'unique:vehicles,vin',
            ],
            'engine_type' => ['required', 'string', 'max:100'],
            'current_mileage' => ['required', 'numeric', 'min:0'],
        ], [
            'brand.in' => 'Brand must be either Audi or Golf.',
            'year.max' => 'Car year cannot be greater than 2026.',
            'plate_number.regex' => 'Plate number must start with B, G, Z, S, N, A, or Y followed by 3 to 7 numbers. Example: B3451.',
            'plate_number.unique' => 'This plate number already exists.',
            'vin.regex' => 'VIN can only contain letters and numbers and cannot contain I, O, or Q.',
            'vin.unique' => 'This VIN already exists.',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $request->user()->id,
            'brand' => $fields['brand'],
            'model' => $fields['model'],
            'color' => $fields['color'],
            'year' => $fields['year'],
            'plate_number' => strtoupper($fields['plate_number']),
            'vin' => strtoupper($fields['vin']),
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