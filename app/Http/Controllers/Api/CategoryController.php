<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // List categories
    public function index(Request $request)
    {
        $query = Category::with('parent:id,name');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->boolean('status'));
        }

        $categories = $query
            ->latest()
            ->paginate(10);

        return response()->json($categories);
    }

    // Create category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $slug = $this->generateSlug($validated['name']);

        $category = Category::create([
            ...$validated,
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    // Show category
    public function show(Category $category)
    {
        return response()->json([
            'data' => $category->load('parent', 'children'),
        ]);
    }

    // Update category
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn([$category->id]),
            ],
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        if ($validated['parent_id'] !== null) {
            $isDescendant = Category::where('parent_id', $category->id)
                ->where('id', $validated['parent_id'])
                ->exists();

            if ($isDescendant) {
                return response()->json([
                    'message' => 'A category cannot be its own descendant.',
                ], 422);
            }
        }

        $category->update([
            ...$validated,
            'slug' => $this->generateSlug(
                $validated['name'],
                $category->id
            ),
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category,
        ]);
    }

    // Delete category
    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Please delete or move child categories first.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    // Generate unique slug
    private function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Category::withTrashed()
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
