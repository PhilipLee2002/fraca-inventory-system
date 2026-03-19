<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $query = Category::withCount('products')->orderBy('name');

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // If per_page is requested, paginate; otherwise return all (for dropdowns)
            if ($request->filled('per_page') || $request->filled('page')) {
                $perPage = min((int) $request->get('per_page', 20), 100);
                $categories = $query->paginate($perPage);
                return $this->sendPaginated($categories, 'Categories retrieved successfully');
            }

            $categories = $query->get();
            return $this->sendSuccess($categories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving categories: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
            ]);
            $category = Category::create($data);
            return $this->sendCreated($category, 'Category created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Error creating category: ' . $e->getMessage());
        }
    }

    public function show(Category $category)
    {
        return $this->sendSuccess($category, 'Category retrieved successfully');
    }

    public function update(Request $request, Category $category)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string',
            ]);
            $category->update($data);
            return $this->sendUpdated($category, 'Category updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Error updating category: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->products()->exists()) {
                return $this->sendError('Cannot delete category with existing products.', [], 422);
            }
            $category->delete();
            return $this->sendDeleted('Category deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting category: ' . $e->getMessage());
        }
    }
}
