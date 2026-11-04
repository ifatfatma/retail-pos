<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Paginated list
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    // All active customers for POS dropdown
    public function all()
    {
        $customers = Customer::where('status', true)
            ->select('id', 'name', 'phone', 'email')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $customers
        ]);
    }

    // Create customer
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'status' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer added successfully.',
            'data' => $customer,
        ], 201);
    }

    // Show customer
    public function show(Customer $customer)
    {
        return response()->json([
            'data' => $customer->load(['sales' => function ($q) {
                $q->latest()->take(10);
            }]),
        ]);
    }

    // Update customer
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'status' => 'boolean',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully.',
            'data' => $customer,
        ]);
    }

    // Delete customer
    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return response()->json([
                'message' => 'Cannot delete customer with sales records.',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully.',
        ]);
    }
}

