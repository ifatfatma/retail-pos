<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    // List units
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->boolean('status'));
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    // Create unit
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'short_name' => 'required|string|max:20|unique:units,short_name',
            'allow_decimal' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        $unit = Unit::create($validated);

        return response()->json([
            'message' => 'Unit created successfully.',
            'data' => $unit,
        ], 201);
    }

    // Show unit
    public function show(Unit $unit)
    {
        return response()->json([
            'data' => $unit,
        ]);
    }

    // Update unit
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',

            'short_name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'short_name')->ignore($unit->id),
            ],

            'allow_decimal' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        $unit->update($validated);

        return response()->json([
            'message' => 'Unit updated successfully.',
            'data' => $unit,
        ]);
    }

    // Delete unit
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully.',
        ]);
    }
}
