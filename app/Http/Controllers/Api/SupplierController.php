<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Paginated list
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('company_name_ar', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->boolean('status'));
        }

        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    // All active suppliers for dropdowns
    public function all()
    {
        $suppliers = Supplier::where('status', true)
            ->select('id', 'name', 'company_name', 'phone')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $suppliers
        ]);
    }

    // Create supplier
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
            'company_name' => 'nullable|string|max:150',
            'company_name_ar' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Supplier added successfully.',
            'data' => $supplier,
        ], 201);
    }

    // Show supplier
    public function show(Supplier $supplier)
    {
        return response()->json([
            'data' => $supplier->loadCount('purchases'),
        ]);
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'name_ar' => 'nullable|string|max:150',
            'company_name' => 'nullable|string|max:150',
            'company_name_ar' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'status' => 'boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'data' => $supplier,
        ]);
    }

    // Delete supplier
    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return response()->json([
                'message' => 'Cannot delete supplier with existing purchase records.',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully.',
        ]);
    }
}

