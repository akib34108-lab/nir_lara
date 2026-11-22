<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialInventory;
use App\Models\MaterialIssue;
use App\Models\Projects;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialIssueController extends Controller
{
    public function index(Request $request)
    {
        $issues = MaterialIssue::with(['project', 'warehouse', 'requester', 'issuer'])
            ->when($request->filled('search'), fn ($query) => $query->where('issue_number', 'like', '%'.$request->search.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()->paginate(10);
        $projects = Projects::orderBy('project_name')->get();

        return view('material-issues.index', compact('issues', 'projects'));
    }

    public function create()
    {
        $projects = Projects::orderBy('project_name')->get();
        $warehouses = Warehouse::where('status', 1)->orderBy('name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();

        return view('material-issues.create', compact('projects', 'warehouses', 'materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'site' => 'nullable|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'issue_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $issue = DB::transaction(function () use ($validated, $request) {
            $nextId = (MaterialIssue::withTrashed()->max('id') ?? 0) + 1;
            $issue = MaterialIssue::create([
                'issue_number' => 'MI-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT),
                'project_id' => $validated['project_id'],
                'site' => $validated['site'] ?? null,
                'warehouse_id' => $validated['warehouse_id'],
                'requested_by' => $request->user()->id,
                'issue_date' => $validated['issue_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'Draft',
            ]);
            foreach ($validated['items'] as $item) {
                $issue->items()->create($item);
            }

            return $issue;
        });

        return redirect()->route('material-issues.show', $issue)->with('success', 'Material issue created successfully.');
    }

    public function show(MaterialIssue $material_issue)
    {
        $material_issue->load(['project', 'warehouse', 'requester', 'issuer', 'approver', 'items.material']);

        return view('material-issues.show', compact('material_issue'));
    }

    public function destroy(MaterialIssue $material_issue)
    {
        if ($material_issue->status !== 'Draft') {
            return redirect()->route('material-issues.index')->with('error', 'Only draft issues can be deleted.');
        }
        $material_issue->delete();

        return redirect()->route('material-issues.index')->with('success', 'Material issue deleted successfully.');
    }

    public function confirm(Request $request, MaterialIssue $material_issue)
    {
        DB::transaction(function () use ($request, $material_issue) {
            $lockedIssue = MaterialIssue::whereKey($material_issue->id)->lockForUpdate()->firstOrFail();
            if ($lockedIssue->status !== 'Draft') {
                abort(422, 'Only draft material issues can be confirmed.');
            }
            if (StockTransaction::where('reference_type', MaterialIssue::class)
                ->where('reference_id', $lockedIssue->id)->exists()) {
                abort(422, 'This material issue has already been posted.');
            }

            foreach ($lockedIssue->items()->with('material')->lockForUpdate()->get() as $item) {
                $inventory = MaterialInventory::where('warehouse_id', $lockedIssue->warehouse_id)
                    ->where('material_id', $item->material_id)
                    ->lockForUpdate()->first();
                $quantity = (float) $item->quantity;
                $available = $inventory ? (float) $inventory->quantity - (float) $inventory->reserved_quantity : 0;
                if ($available + 0.0001 < $quantity) {
                    abort(422, 'Insufficient stock for '.$item->material->name.'.');
                }
                $inventory->quantity = (float) $inventory->quantity - $quantity;
                $inventory->save();
                StockTransaction::create([
                    'warehouse_id' => $lockedIssue->warehouse_id,
                    'material_id' => $item->material_id,
                    'transaction_type' => 'MATERIAL_ISSUE',
                    'quantity' => -$quantity,
                    'balance_quantity' => $inventory->quantity,
                    'reference_type' => MaterialIssue::class,
                    'reference_id' => $lockedIssue->id,
                    'created_by' => $request->user()->id,
                    'notes' => 'Material issue '.$lockedIssue->issue_number,
                ]);
            }

            $lockedIssue->update([
                'status' => 'Confirmed',
                'issued_by' => $request->user()->id,
                'approved_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);
        });

        return redirect()->route('material-issues.show', $material_issue)->with('success', 'Material issue confirmed and stock reduced.');
    }
}
