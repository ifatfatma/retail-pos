<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    // List brands
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->boolean('status'));
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    // Create brand
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|boolean',
        ]);

        $logo = null;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo')
                ->store('brands', 'public');
        }

        $brand = Brand::create([
            'name' => $validated['name'],
            'slug' => $this->generateSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'logo' => $logo,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Brand created successfully.',
            'data' => $brand,
        ], 201);
    }

    // Show brand
    public function show(Brand $brand)
    {
        return response()->json([
            'data' => $brand,
        ]);
    }

    // Update brand
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|boolean',
        ]);

        $logo = $brand->logo;

        if ($request->hasFile('logo')) {

            if ($logo) {
                Storage::disk('public')->delete($logo);
            }

            $logo = $request->file('logo')
                ->store('brands', 'public');
        }

        $brand->update([
            'name' => $validated['name'],
            'slug' => $this->generateSlug(
                $validated['name'],
                $brand->id
            ),
            'description' => $validated['description'] ?? null,
            'logo' => $logo,
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Brand updated successfully.',
            'data' => $brand,
        ]);
    }

    // Delete brand
    public function destroy(Brand $brand)
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return response()->json([
            'message' => 'Brand deleted successfully.',
        ]);
    }

    // Generate unique slug
    private function generateSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Brand::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) =>
                    $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
