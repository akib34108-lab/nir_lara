<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialInventory;
use App\Models\MaterialReturn;
use App\Models\Projects;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = MaterialReturn::with(['project', 'warehouse', 'supplier', 'responsibleUser'])
            ->when($request->filled('search'), fn ($query) => $query->where('return_number', 'like', '%'.$request->search.'%'))
            ->when($request->filled('type'), fn ($query) => $query->where('return_type', $request->type))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()->paginate(10);

        return view('material-returns.index', compact('returns'));
    }

    public function create()
    {
        $projects = Projects::orderBy('project_name')->get();
        $warehouses = Warehouse::where('status', 1)->orderBy('name')->get();
        $suppliers = Supplier::where('status', 1)->orderBy('company_name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();

        return view('material-returns.create', compact('projects', 'warehouses', 'suppliers', 'materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_type' => 'required|in:Warehouse,Supplier',
            'project_id' => 'nullable|exists:projects,id',
            'site' => 'nullable|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|required_if:return_type,Supplier|exists:suppliers,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $return = DB::transaction(function () use ($validated, $request) {
            $nextId = (MaterialReturn::withTrashed()->max('id') ?? 0) + 1;
            $return = MaterialReturn::create([
                'return_number' => 'MR-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT),
                'return_type' => $validated['return_type'],
                'project_id' => $validated['project_id'] ?? null,
                'site' => $validated['site'] ?? null,
                'warehouse_id' => $validated['warehouse_id'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'responsible_user_id' => $request->user()->id,
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'] ?? null,
                'status' => 'Draft',
            ]);
            foreach ($validated['items'] as $item) {
                $return->items()->create($item);
            }

            return $return;
        });

        return redirect()->route('material-returns.show', $return)->with('success', 'Material return created successfully.');
    }

    public function show(MaterialReturn $material_return)
    {
        $material_return->load(['project', 'warehouse', 'supplier', 'responsibleUser', 'approver', 'items.material']);

        return view('material-returns.show', compact('material_return'));
    }

    public function destroy(MaterialReturn $material_return)
    {
        if ($material_return->status !== 'Draft') {
            return redirect()->route('material-returns.index')->with('error', 'Only draft returns can be deleted.');
        }
        $material_return->delete();

        return redirect()->route('material-returns.index')->with('success', 'Material return deleted successfully.');
    }

    public function process(Request $request, MaterialReturn $material_return)
    {
        $validated = $request->validate([
            'accepted_quantity' => 'sometimes|array',
            'accepted_quantity.*' => 'numeric|min:0',
            'rejected_quantity' => 'sometimes|array',
            'rejected_quantity.*' => 'numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request, $material_return) {
            $lockedReturn = MaterialReturn::whereKey($material_return->id)->lockForUpdate()->firstOrFail();
            if (! in_array($lockedReturn->status, ['Draft', 'Approved'], true)) {
                abort(422, 'This material return cannot be processed.');
            }
            if (StockTransaction::where('reference_type', MaterialReturn::class)
                ->where('reference_id', $lockedReturn->id)->exists()) {
                abort(422, 'This material return has already been posted.');
            }

            if ($lockedReturn->return_type === 'Supplier') {
                foreach ($lockedReturn->items()->with('material')->lockForUpdate()->get() as $item) {
                    $quantity = (float) $item->quantity;
                    $inventory = MaterialInventory::where('warehouse_id', $lockedReturn->warehouse_id)
                        ->where('material_id', $item->material_id)
                        ->lockForUpdate()->first();
                    $available = $inventory ? (float) $inventory->quantity - (float) $inventory->reserved_quantity : 0;
                    if ($available + 0.0001 < $quantity) {
                        abort(422, 'Insufficient stock to dispatch supplier return for '.$item->material->name.'.');
                    }
                    $inventory->quantity = (float) $inventory->quantity - $quantity;
                    $inventory->save();
                    StockTransaction::create([
                        'warehouse_id' => $lockedReturn->warehouse_id,
                        'material_id' => $item->material_id,
                        'transaction_type' => 'SUPPLIER_RETURN',
                        'quantity' => -$quantity,
                        'balance_quantity' => $inventory->quantity,
                        'reference_type' => MaterialReturn::class,
                        'reference_id' => $lockedReturn->id,
                        'created_by' => $request->user()->id,
                        'notes' => 'Supplier return '.$lockedReturn->return_number,
                    ]);
                }
                $lockedReturn->update([
                    'status' => 'Dispatched',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);
            } else {
                foreach ($lockedReturn->items()->lockForUpdate()->get() as $item) {
                    $accepted = (float) ($validated['accepted_quantity'][$item->id] ?? 0);
                    $rejected = (float) ($validated['rejected_quantity'][$item->id] ?? 0);
                    if (abs(($accepted + $rejected) - (float) $item->quantity) > 0.0001) {
                        abort(422, 'Accepted plus rejected quantity must equal the returned quantity.');
                    }
                    $item->update(['accepted_quantity' => $accepted, 'rejected_quantity' => $rejected]);
                    if ($accepted <= 0) {
                        continue;
                    }
                    $inventory = MaterialInventory::where('warehouse_id', $lockedReturn->warehouse_id)
                        ->where('material_id', $item->material_id)
                        ->lockForUpdate()->first();
                    if (! $inventory) {
                        $inventory = MaterialInventory::create([
                            'warehouse_id' => $lockedReturn->warehouse_id,
                            'material_id' => $item->material_id,
                            'quantity' => 0,
                            'reserved_quantity' => 0,
                            'average_cost' => 0,
                        ]);
                    }
                    $inventory->quantity = (float) $inventory->quantity + $accepted;
                    $inventory->save();
                    StockTransaction::create([
                        'warehouse_id' => $lockedReturn->warehouse_id,
                        'material_id' => $item->material_id,
                        'transaction_type' => 'WAREHOUSE_RETURN',
                        'quantity' => $accepted,
                        'balance_quantity' => $inventory->quantity,
                        'reference_type' => MaterialReturn::class,
                        'reference_id' => $lockedReturn->id,
                        'created_by' => $request->user()->id,
                        'notes' => 'Accepted warehouse return '.$lockedReturn->return_number,
                    ]);
                }
                $lockedReturn->update([
                    'status' => 'Completed',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);
            }
        });

        return redirect()->route('material-returns.show', $material_return)
            ->with('success', 'Material return processed successfully.');
    }
}
