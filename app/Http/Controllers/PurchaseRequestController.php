<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Projects;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PurchaseRequest::query()
            ->with(['project', 'requester'])
            ->withCount('items')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('request_number', 'like', '%'.$request->search.'%')
                    ->orWhere('purpose', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('project'), function ($query) use ($request) {
                $query->where('project_id', $request->project);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        $projects = Projects::whereNull('deleted_at')->orderBy('project_name')->get();

        return view('purchase-requests.index', compact('requests', 'projects'));
    }

    public function create()
    {
        $projects = Projects::whereNull('deleted_at')->orderBy('project_name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();

        return view('purchase-requests.create', compact('projects', 'materials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.requested_quantity' => 'required|numeric|min:0.001',
            'items.*.required_date' => 'required|date',
        ]);

        $purchaseRequest = DB::transaction(function () use ($validated, $request) {
            $nextId = (PurchaseRequest::withTrashed()->max('id') ?? 0) + 1;
            $requestNumber = 'PR-'.now()->format('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);

            $purchaseRequest = PurchaseRequest::create([
                'request_number' => $requestNumber,
                'project_id' => $validated['project_id'],
                'requester_id' => $request->user()->id,
                'purpose' => $validated['purpose'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'Pending',
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseRequest->items()->create([
                    'material_id' => $item['material_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'approved_quantity' => 0,
                    'required_date' => $item['required_date'],
                ]);
            }

            return $purchaseRequest;
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase request created successfully.');
    }

    public function show(PurchaseRequest $purchase_request)
    {
        $purchase_request->load(['project', 'requester', 'approver', 'items.material']);

        return view('purchase-requests.show', compact('purchase_request'));
    }

    public function edit(PurchaseRequest $purchase_request)
    {
        if ($purchase_request->status !== 'Pending') {
            return redirect()->route('purchase-requests.show', $purchase_request)
                ->with('error', 'Only pending purchase requests can be edited.');
        }

        $purchase_request->load('items');
        $projects = Projects::whereNull('deleted_at')->orderBy('project_name')->get();
        $materials = Material::where('status', 1)->orderBy('name')->get();

        return view('purchase-requests.edit', compact('purchase_request', 'projects', 'materials'));
    }

    public function update(Request $request, PurchaseRequest $purchase_request)
    {
        if ($purchase_request->status !== 'Pending') {
            return redirect()->route('purchase-requests.show', $purchase_request)
                ->with('error', 'Only pending purchase requests can be edited.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.requested_quantity' => 'required|numeric|min:0.001',
            'items.*.required_date' => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $purchase_request) {
            $lockedRequest = PurchaseRequest::whereKey($purchase_request->id)->lockForUpdate()->first();
            if ($lockedRequest->status !== 'Pending') {
                abort(422, 'Only pending purchase requests can be edited.');
            }

            $lockedRequest->update([
                'project_id' => $validated['project_id'],
                'purpose' => $validated['purpose'],
                'notes' => $validated['notes'] ?? null,
            ]);
            $lockedRequest->items()->delete();

            foreach ($validated['items'] as $item) {
                $lockedRequest->items()->create([
                    'material_id' => $item['material_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'approved_quantity' => 0,
                    'required_date' => $item['required_date'],
                ]);
            }
        });

        return redirect()->route('purchase-requests.show', $purchase_request)
            ->with('success', 'Purchase request updated successfully.');
    }

    public function destroy(PurchaseRequest $purchase_request)
    {
        if (! in_array($purchase_request->status, ['Pending', 'Cancelled'], true)) {
            return redirect()->route('purchase-requests.index')
                ->with('error', 'Only pending or cancelled purchase requests can be deleted.');
        }

        $purchase_request->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Purchase request deleted successfully.');
    }

    public function approve(Request $request, PurchaseRequest $purchase_request)
    {
        if ($purchase_request->status !== 'Pending') {
            return redirect()->route('purchase-requests.show', $purchase_request)
                ->with('error', 'Only pending purchase requests can be approved.');
        }

        if ($purchase_request->requester_id === $request->user()->id) {
            abort(403, 'You cannot approve your own purchase request.');
        }

        $validated = $request->validate([
            'approved_quantity' => 'required|array',
            'approved_quantity.*' => 'required|numeric|min:0',
            'approval_remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $purchase_request) {
            $lockedRequest = PurchaseRequest::whereKey($purchase_request->id)->lockForUpdate()->first();
            if ($lockedRequest->status !== 'Pending') {
                abort(422, 'Only pending purchase requests can be approved.');
            }
            if ($lockedRequest->requester_id === $request->user()->id) {
                abort(403, 'You cannot approve your own purchase request.');
            }

            $hasApproved = false;
            $isFullyApproved = true;

            foreach ($lockedRequest->items()->lockForUpdate()->get() as $item) {
                $approvedQuantity = (float) ($validated['approved_quantity'][$item->id] ?? 0);
                if ($approvedQuantity > (float) $item->requested_quantity) {
                    abort(422, 'Approved quantity cannot exceed requested quantity.');
                }

                $item->update(['approved_quantity' => $approvedQuantity]);
                $hasApproved = $hasApproved || $approvedQuantity > 0;
                $isFullyApproved = $isFullyApproved
                    && abs($approvedQuantity - (float) $item->requested_quantity) < 0.0001;
            }

            if (! $hasApproved) {
                abort(422, 'At least one material quantity must be approved.');
            }

            $lockedRequest->update([
                'status' => $isFullyApproved ? 'Approved' : 'Partially Approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'approval_remarks' => $validated['approval_remarks'] ?? null,
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchase_request)
            ->with('success', 'Purchase request approval saved successfully.');
    }

    public function reject(Request $request, PurchaseRequest $purchase_request)
    {
        if ($purchase_request->status !== 'Pending') {
            return redirect()->route('purchase-requests.show', $purchase_request)
                ->with('error', 'Only pending purchase requests can be rejected.');
        }
        if ($purchase_request->requester_id === $request->user()->id) {
            abort(403, 'You cannot reject your own purchase request.');
        }

        $validated = $request->validate([
            'approval_remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $purchase_request) {
            $lockedRequest = PurchaseRequest::whereKey($purchase_request->id)->lockForUpdate()->first();
            if ($lockedRequest->status !== 'Pending') {
                abort(422, 'Only pending purchase requests can be rejected.');
            }
            if ($lockedRequest->requester_id === $request->user()->id) {
                abort(403, 'You cannot reject your own purchase request.');
            }

            $lockedRequest->update([
                'status' => 'Rejected',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'approval_remarks' => $validated['approval_remarks'] ?? null,
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchase_request)
            ->with('success', 'Purchase request rejected.');
    }

    public function cancel(PurchaseRequest $purchase_request)
    {
        if ($purchase_request->status !== 'Pending') {
            return redirect()->route('purchase-requests.show', $purchase_request)
                ->with('error', 'Only pending purchase requests can be cancelled.');
        }

        if ($purchase_request->requester_id !== auth()->id()) {
            abort(403, 'Only the requester can cancel this purchase request.');
        }

        $purchase_request->update([
            'status' => 'Cancelled',
            'approval_remarks' => 'Cancelled by requester.',
        ]);

        return redirect()->route('purchase-requests.show', $purchase_request)
            ->with('success', 'Purchase request cancelled.');
    }
}
