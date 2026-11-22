<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialConsumption;
use App\Models\MaterialIssue;
use App\Models\Projects;
use Illuminate\Http\Request;

class MaterialConsumptionController extends Controller
{
    public function index(Request $request)
    {
        $consumptions = MaterialConsumption::with(['project', 'material', 'issue', 'warehouse', 'recorder'])
            ->when($request->filled('project'), fn ($query) => $query->where('project_id', $request->project))
            ->when($request->filled('material'), fn ($query) => $query->where('material_id', $request->material))
            ->latest()->paginate(10);
        $projects = Projects::orderBy('project_name')->get();
        $materials = Material::orderBy('name')->get();

        return view('material-consumptions.index', compact('consumptions', 'projects', 'materials'));
    }

    public function create()
    {
        $projects = Projects::orderBy('project_name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();
        $issues = MaterialIssue::where('status', 'Confirmed')->with(['project', 'warehouse', 'items.material'])->latest()->get();

        return view('material-consumptions.create', compact('projects', 'materials', 'issues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'site' => 'nullable|string|max:255',
            'material_issue_id' => 'nullable|exists:material_issues,id',
            'material_id' => 'required|exists:materials,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'issued_quantity' => 'required|numeric|min:0',
            'estimated_quantity' => 'required|numeric|min:0',
            'consumed_quantity' => 'required|numeric|min:0',
            'wasted_quantity' => 'required|numeric|min:0',
            'consumption_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $remaining = (float) $validated['issued_quantity']
            - (float) $validated['consumed_quantity']
            - (float) $validated['wasted_quantity'];
        if ($remaining < -0.0001) {
            return back()->withInput()->withErrors(['consumed_quantity' => 'Consumed plus wasted quantity cannot exceed issued quantity.']);
        }

        if (! empty($validated['material_issue_id'])) {
            $issue = MaterialIssue::with('items')->findOrFail($validated['material_issue_id']);
            if ($issue->status !== 'Confirmed') {
                return back()->withInput()->withErrors(['material_issue_id' => 'The selected issue must be confirmed.']);
            }
            $issueItem = $issue->items->firstWhere('material_id', (int) $validated['material_id']);
            if (! $issueItem || (float) $validated['issued_quantity'] > (float) $issueItem->quantity + 0.0001) {
                return back()->withInput()->withErrors(['issued_quantity' => 'Issued quantity exceeds the confirmed issue quantity.']);
            }
        }

        MaterialConsumption::create($validated + [
            'remaining_quantity' => max(0, $remaining),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('material-consumptions.index')
            ->with('success', 'Material consumption recorded successfully.');
    }

    public function show(MaterialConsumption $material_consumption)
    {
        $material_consumption->load(['project', 'material', 'issue', 'warehouse', 'recorder']);

        return view('material-consumptions.show', compact('material_consumption'));
    }

    public function destroy(MaterialConsumption $material_consumption)
    {
        $material_consumption->delete();

        return redirect()->route('material-consumptions.index')
            ->with('success', 'Material consumption record deleted successfully.');
    }
}
