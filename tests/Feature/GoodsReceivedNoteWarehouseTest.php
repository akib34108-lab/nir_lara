<?php

namespace Tests\Feature;

use App\Models\GoodsReceivedNote;
use App\Models\GRNItem;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialInspection;
use App\Models\MaterialInventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceivedNoteWarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_typed_warehouse_name_is_used_for_a_grn(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        [$purchaseOrder, $item] = $this->createApprovedPurchaseOrder($user);

        $this->actingAs($user)
            ->get(route('grns.create', ['purchase_order' => $purchaseOrder->id]))
            ->assertOk()
            ->assertSee('name="warehouse_name"', false)
            ->assertSee('type="text"', false)
            ->assertDontSee('name="warehouse_id"', false);

        $this->actingAs($user)->post(route('grns.store'), [
            'purchase_order_id' => $purchaseOrder->id,
            'warehouse_name' => 'Remote Storage',
            'received_date' => '2026-11-23',
            'items' => [
                [
                    'purchase_order_item_id' => $item->id,
                    'material_id' => $item->material_id,
                    'received_quantity' => 5,
                ],
            ],
        ])->assertRedirect();

        $warehouse = Warehouse::where('name', 'Remote Storage')->firstOrFail();
        $grn = GoodsReceivedNote::where('purchase_order_id', $purchaseOrder->id)->firstOrFail();
        $grnItem = GRNItem::where('goods_received_note_id', $grn->id)->firstOrFail();

        $this->assertSame($warehouse->id, $grn->warehouse_id);
        $this->assertSame(1, $warehouse->status);

        $inspection = MaterialInspection::create([
            'goods_received_note_id' => $grn->id,
            'grn_item_id' => $grnItem->id,
            'inspector_id' => $user->id,
            'inspected_quantity' => 5,
            'accepted_quantity' => 5,
            'rejected_quantity' => 0,
            'inspection_date' => '2026-11-23',
            'quality_status' => 'Accepted',
        ]);
        $grnItem->update([
            'accepted_quantity' => 5,
            'rejected_quantity' => 0,
        ]);
        $grn->update(['status' => 'Inspected']);

        $this->actingAs($user)
            ->get(route('grns.show', $grn))
            ->assertOk()
            ->assertSee('Confirm this GRN to post accepted material quantities to inventory.');

        $this->assertSame($grnItem->id, $inspection->grnItem->id);

        $this->actingAs($user)
            ->post(route('grns.confirm', $grn))
            ->assertRedirect(route('grns.show', $grn));

        $inventory = MaterialInventory::where('warehouse_id', $warehouse->id)
            ->where('material_id', $item->material_id)
            ->firstOrFail();
        $this->assertEquals(5, (float) $inventory->quantity);
    }

    public function test_grn_pages_show_the_purchase_order_even_if_it_was_soft_deleted(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        [$purchaseOrder] = $this->createApprovedPurchaseOrder($user);
        $warehouse = Warehouse::create([
            'code' => 'WH-GRN-TEST',
            'name' => 'GRN Test Warehouse',
            'status' => 1,
        ]);
        $grn = GoodsReceivedNote::create([
            'grn_number' => 'GRN-2026-TEST',
            'purchase_order_id' => $purchaseOrder->id,
            'warehouse_id' => $warehouse->id,
            'received_by' => $user->id,
            'received_date' => '2026-11-23',
            'status' => 'Draft',
        ]);
        $purchaseOrder->delete();

        $this->actingAs($user)
            ->get(route('grns.index'))
            ->assertOk()
            ->assertSee($purchaseOrder->po_number);

        $this->actingAs($user)
            ->get(route('grns.show', $grn))
            ->assertOk()
            ->assertSee($purchaseOrder->po_number);
    }

    private function createApprovedPurchaseOrder(User $user): array
    {
        $supplier = Supplier::create([
            'supplier_code' => 'SUP-GRN-TEST',
            'company_name' => 'GRN Test Supplier',
            'phone' => '1234567890',
        ]);
        $category = MaterialCategory::create(['name' => 'GRN Test Category']);
        $material = Material::create([
            'material_code' => 'MAT-GRN-TEST',
            'name' => 'GRN Test Material',
            'category_id' => $category->id,
            'unit' => 'piece',
        ]);
        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-GRN-TEST',
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'order_date' => '2026-11-20',
            'status' => 'Approved',
        ]);
        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $purchaseOrder->id,
            'material_id' => $material->id,
            'ordered_quantity' => 5,
            'unit_price' => 10,
            'subtotal' => 50,
        ]);

        return [$purchaseOrder, $item];
    }
}
