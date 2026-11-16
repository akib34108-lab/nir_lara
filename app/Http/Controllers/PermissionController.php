<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::latest()->paginate(10);
        return view('user_access.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user_access.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:100|regex:/^[a-zA-Z0-9]+$/',
            'action' => 'required|in:view,create,edit,delete',
        ]);
        $name = strtolower($request->module) . '-' . $request->action;
        $request->merge(['name' => $name]);
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);
        Permission::create([
            'name' => $name,
            'guard_name' => 'web',
        ]);
        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $parts = explode('-', $permission->name, 2);
        $module = $parts[0];
        $action = $parts[1] ?? '';
        return view('user_access.permissions.edit', compact(
            'permission',
            'module',
            'action'
        ));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'module' => 'required|string|max:100|regex:/^[a-zA-Z0-9]+$/',
            'action' => 'required|in:view,create,edit,delete',
        ]);
        $name = strtolower($request->module) . '-' . $request->action;
        Validator::make(['name' => $name], [
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ])->validate();

        $permission->update([
            'name' => $name,
        ]);
        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
