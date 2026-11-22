<?php

namespace App\Http\Controllers;

use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = MaterialCategory::query()
            ->withCount('materials')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('material-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('material-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:material_categories,name',
            'description' => 'nullable|string',
            'status' => 'required|integer|in:1,2',
        ]);

        MaterialCategory::create($validated);

        return redirect()->route('material-categories.index')
            ->with('success', 'Material category created successfully.');
    }

    public function show(MaterialCategory $material_category)
    {
        $material_category->loadCount('materials');

        return view('material-categories.show', compact('material_category'));
    }

    public function edit(MaterialCategory $material_category)
    {
        return view('material-categories.edit', compact('material_category'));
    }

    public function update(Request $request, MaterialCategory $material_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:material_categories,name,'.$material_category->id,
            'description' => 'nullable|string',
            'status' => 'required|integer|in:1,2',
        ]);

        $material_category->update($validated);

        return redirect()->route('material-categories.index')
            ->with('success', 'Material category updated successfully.');
    }

    public function destroy(MaterialCategory $material_category)
    {
        if ($material_category->materials()->exists()) {
            return redirect()->route('material-categories.index')
                ->with('error', 'This category cannot be deleted because it is used by existing materials.');
        }

        $material_category->delete();

        return redirect()->route('material-categories.index')
            ->with('success', 'Material category deleted successfully.');
    }
}
