<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\GRNItem;
use App\Models\MaterialInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialInspectionController extends Controller
{
    public function index(Request $request)
    {
        $inspections = MaterialInspection::query()
            ->with(['grn', 'grnItem.material', 'inspector'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('quality_status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $grns = GoodsReceivedNote::whereIn('status', ['Draft', 'Inspected'])
            ->with(['purchaseOrder', 'items.material'])
            ->latest()
            ->get();
        $selectedGrn = $request->filled('grn')
            ? $grns->firstWhere('id', (int) $request->grn)
            : null;

        return view('inspections.create', compact('grns', 'selectedGrn'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'goods_received_note_id' => 'required|exists:goods_received_notes,id',
            'items' => 'required|array|min:1',
            'items.*.grn_item_id' => 'required|exists:grn_items,id',
            'items.*.inspected_quantity' => 'required|numeric|min:0.001',
            'items.*.accepted_quantity' => 'required|numeric|min:0',
            'items.*.rejected_quantity' => 'required|numeric|min:0',
            'items.*.inspection_date' => 'required|date',
            'items.*.quality_status' => 'required|in:Accepted,Rejected,Partially Accepted',
            'items.*.remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $grn = GoodsReceivedNote::whereKey($validated['goods_received_note_id'])
                ->lockForUpdate()
                ->firstOrFail();
            if (in_array($grn->status, ['Confirmed', 'Cancelled'], true)) {
                abort(422, 'This GRN can no longer be inspected.');
            }

            foreach ($validated['items'] as $item) {
                $grnItem = GRNItem::whereKey($item['grn_item_id'])
                    ->where('goods_received_note_id', $grn->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $inspected = (float) $item['inspected_quantity'];
                $accepted = (float) $item['accepted_quantity'];
                $rejected = (float) $item['rejected_quantity'];
                if (abs(($accepted + $rejected) - $inspected) > 0.0001) {
                    abort(422, 'Accepted plus rejected quantity must equal inspected quantity.');
                }
                if ($inspected > (float) $grnItem->received_quantity + 0.0001) {
                    abort(422, 'Inspected quantity cannot exceed received quantity.');
                }

                MaterialInspection::create([
                    'goods_received_note_id' => $grn->id,
                    'grn_item_id' => $grnItem->id,
                    'inspector_id' => $request->user()->id,
                    'inspected_quantity' => $inspected,
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'inspection_date' => $item['inspection_date'],
                    'quality_status' => $item['quality_status'],
                    'remarks' => $item['remarks'] ?? null,
                ]);

                $grnItem->update([
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                ]);
            }

            $uninspected = $grn->items()
                ->whereDoesntHave('inspections')
                ->exists();
            $grn->update(['status' => $uninspected ? 'Draft' : 'Inspected']);
        });

        return redirect()->route('grns.index')
            ->with('success', 'Material inspection recorded successfully.');
    }
}
