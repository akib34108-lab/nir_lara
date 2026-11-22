<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($searchQuery) use ($request) {
                    $searchQuery->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('material_code', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        $categories = MaterialCategory::where('status', 1)->orderBy('name')->get();

        return view('materials.index', compact('materials', 'categories'));
    }

    public function create()
    {
        $nextId = (Material::withTrashed()->max('id') ?? 0) + 1;
        $nextMaterialCode = 'MAT-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $categories = MaterialCategory::where('status', 1)->orderBy('name')->get();

        return view('materials.create', compact('nextMaterialCode', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_code' => 'required|string|max:50|unique:materials,material_code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:material_categories,id',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'minimum_stock_level' => 'required|numeric|min:0',
            'status' => 'required|integer|in:1,2',
        ]);

        Material::create($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Material created successfully.');
    }

    public function show(Material $material)
    {
        $material->load('category');

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        $categories = MaterialCategory::where('status', 1)->orderBy('name')->get();

        return view('materials.edit', compact('material', 'categories'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'material_code' => 'required|string|max:50|unique:materials,material_code,'.$material->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:material_categories,id',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'minimum_stock_level' => 'required|numeric|min:0',
            'status' => 'required|integer|in:1,2',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material deleted successfully.');
    }
}
