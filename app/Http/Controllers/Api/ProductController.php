<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // List products
    public function index(Request $request)
    {
       $query = Product::with([
    'category:id,name',
    'brand:id,name',
    'unit:id,name',
    'variants'
]);

        // Search product name, SKU or barcode
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($v) use ($search) {
                        $v->where('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Unit filter
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->boolean('status'));
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    // Create product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            'product_type' => 'required|in:simple',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:100|unique:product_variants,barcode',
            'track_inventory' => 'required|boolean',
            'low_stock_threshold' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $product = DB::transaction(function () use ($validated) {

            $product = Product::create([
                'name' => $validated['name'],
                'slug' => $this->generateSlug($validated['name']),
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'description' => $validated['description'] ?? null,
                'product_type' => 'simple',
                'track_inventory' => $validated['track_inventory'],
                'low_stock_threshold' => $validated['low_stock_threshold'],
                'status' => $validated['status'],
            ]);

            // Generate unique SKU using product ID
            $sku = 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'variant_name' => 'Standard',
                'sku' => $sku,
                'barcode' => $validated['barcode'] ?? null,
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
                'mrp' => $validated['mrp'],
                'tax_rate' => $validated['tax_rate'],
                'is_default' => true,
                'status' => $validated['status'],
            ]);

            Stock::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 0,
            ]);

            return $product;
        });

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load([
                'category',
                'brand',
                'unit',
                'variants'
                
            ]),
        ], 201);
    }

    // Show product
    public function show(Product $product)
    {
        return response()->json([
            'data' => $product->load([
                'category',
                'brand',
                'unit',
                'variants',
                'images'
            ]),
        ]);
    }

    // Update product
    public function update(Request $request, Product $product)
    {
        $variant = $product->variants()
            ->where('is_default', true)
            ->first();

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:100|unique:product_variants,barcode,' . ($variant?->id ?? 0),
            'track_inventory' => 'required|boolean',
            'low_stock_threshold' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $product, $variant) {

            $product->update([
                'name' => $validated['name'],
                'slug' => $this->generateSlug(
                    $validated['name'],
                    $product->id
                ),
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'description' => $validated['description'] ?? null,
                'track_inventory' => $validated['track_inventory'],
                'low_stock_threshold' => $validated['low_stock_threshold'],
                'status' => $validated['status'],
            ]);

            if ($variant) {
                $variant->update([
                    'barcode' => $validated['barcode'] ?? null,
                    'purchase_price' => $validated['purchase_price'],
                    'selling_price' => $validated['selling_price'],
                    'mrp' => $validated['mrp'],
                    'tax_rate' => $validated['tax_rate'],
                    'status' => $validated['status'],
                ]);
            }
        });

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->fresh()->load([
                'category',
                'brand',
                'unit',
                'variants'
            ]),
        ]);
    }

    // Delete product
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
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
            Product::withTrashed()
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


    public function uploadImages(Request $request, Product $product)
{
    $validated = $request->validate([
        'images' => 'required|array|min:1',
        'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $uploadedImages = [];

    foreach ($request->file('images') as $image) {
        $path = $image->store('products', 'public');

        $productImage = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path,
            'alt_text' => $product->name,
            'is_primary' => !$product->images()->exists(),
            'sort_order' => $product->images()->count(),
        ]);

        $uploadedImages[] = [
            'id' => $productImage->id,
            'image_path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }

    return response()->json([
        'message' => 'Product images uploaded successfully.',
        'data' => $uploadedImages,
    ], 201);
}

}
