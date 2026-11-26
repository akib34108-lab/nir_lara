<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\GoodsReceivedNote;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Projects;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SupplierBasedPurchaseOrderGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_material_menu_is_expanded_on_purchase_order_pages(): void
    {
        [$user] = $this->createApprovedRequest();

        $this->actingAs($user)
            ->get(route('purchase-orders.index'))
            ->assertOk()
            ->assertSee('href="#material-menu" aria-expanded="true"', false)
            ->assertSee('class="collapse show" id="material-menu"', false)
            ->assertSee('class="collapse" id="users-access-menu"', false)
            ->assertSee('class="collapse" id="projects-menu"', false)
            ->assertDontSee('class="collapse show" id="users-access-menu"', false)
            ->assertDontSee('class="collapse show" id="projects-menu"', false);
    }

    public function test_approved_request_items_are_grouped_by_supplier_and_can_be_ordered_partially(): void
    {
        [$user, $purchaseRequest, $items, $supplierA, $supplierB] = $this->createApprovedRequest();

        $this->actingAs($user)
            ->get(route('purchase-requests.purchase-orders.create', $purchaseRequest))
            ->assertOk()
            ->assertSee('Remaining');

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 0.0004,
                        'unit_price' => 1.001,
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                "items.{$items[0]->id}.ordered_quantity",
                "items.{$items[0]->id}.unit_price",
            ]);

        $this->assertDatabaseCount('purchase_orders', 0);

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                        'discount' => 10.01,
                        'tax' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors("items.{$items[0]->id}.discount");

        $this->assertDatabaseCount('purchase_orders', 0);

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                "items.{$items[0]->id}.discount",
                "items.{$items[0]->id}.tax",
            ]);

        $this->assertDatabaseCount('purchase_orders', 0);

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 5,
                        'unit_price' => 10,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                    $items[1]->id => [
                        'supplier_id' => $supplierB->id,
                        'ordered_quantity' => 6.001,
                        'unit_price' => 3,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors("items.{$items[1]->id}.ordered_quantity");

        $this->assertDatabaseCount('purchase_orders', 0);

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 5,
                        'unit_price' => 10,
                        'discount' => 2,
                        'tax' => 5,
                    ],
                    $items[1]->id => [
                        'supplier_id' => $supplierB->id,
                        'ordered_quantity' => 6,
                        'unit_price' => 3,
                        'discount' => 1,
                        'tax' => 2,
                    ],
                    $items[2]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 3,
                        'unit_price' => 4,
                        'discount' => 0.50,
                        'tax' => 0.75,
                    ],
                ],
            ])
            ->assertRedirect();

        $orders = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
            ->with('items')
            ->get();
        $this->assertCount(2, $orders);

        $supplierAOrder = $orders->firstWhere('supplier_id', $supplierA->id);
        $supplierBOrder = $orders->firstWhere('supplier_id', $supplierB->id);

        $this->assertNotNull($supplierAOrder);
        $this->assertNotNull($supplierBOrder);
        $this->assertSame('PO-2026-'.str_pad((string) $supplierAOrder->id, 5, '0', STR_PAD_LEFT), $supplierAOrder->po_number);
        $this->assertEquals(2.5, (float) $supplierAOrder->discount);
        $this->assertEquals(5.75, (float) $supplierAOrder->tax);
        $this->assertEquals(65.25, (float) $supplierAOrder->grand_total);
        $this->assertCount(2, $supplierAOrder->items);
        $this->assertEquals(1, (float) $supplierBOrder->discount);
        $this->assertEquals(2, (float) $supplierBOrder->tax);
        $this->assertEquals(19, (float) $supplierBOrder->grand_total);
        $this->assertCount(1, $supplierBOrder->items);
        $this->assertSame($purchaseRequest->id, $supplierAOrder->purchase_request_id);
        $this->assertSame($items[0]->id, $supplierAOrder->items->firstWhere('material_id', $items[0]->material_id)->purchase_request_item_id);

        $this->actingAs($user)
            ->get(route('purchase-requests.purchase-orders.create', $purchaseRequest))
            ->assertOk()
            ->assertSee('5.000');

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 5,
                        'unit_price' => 11,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('purchase_orders', 3);

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 0.001,
                        'unit_price' => 11,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors("items.{$items[0]->id}.ordered_quantity");

        $this->assertDatabaseCount('purchase_orders', 3);

        $supplierAOrder->update(['status' => 'Approved']);
        $warehouse = Warehouse::create([
            'code' => 'WH-SUPPLIER-PO-TEST',
            'name' => 'Supplier PO Test Warehouse',
            'status' => 1,
        ]);
        GoodsReceivedNote::create([
            'grn_number' => 'GRN-SUPPLIER-PO-TEST',
            'purchase_order_id' => $supplierAOrder->id,
            'warehouse_id' => $warehouse->id,
            'received_by' => $user->id,
            'received_date' => '2026-11-25',
            'status' => 'Draft',
        ]);

        $this->actingAs($user)
            ->post(route('purchase-orders.cancel', $supplierAOrder))
            ->assertStatus(422);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $supplierAOrder->id,
            'status' => 'Approved',
        ]);

        $this->actingAs($user)
            ->get(route('grns.create'))
            ->assertOk()
            ->assertSee($supplierAOrder->po_number);
    }

    public function test_generation_routes_require_purchase_order_create_permission(): void
    {
        [, $purchaseRequest, $items, $supplierA] = $this->createApprovedRequest();
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser)
            ->get(route('purchase-requests.purchase-orders.create', $purchaseRequest))
            ->assertForbidden();

        $this->actingAs($unauthorizedUser)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_create_only_users_are_returned_to_a_page_they_can_access(): void
    {
        [, $purchaseRequest, $items, $supplierA] = $this->createApprovedRequest();
        $createOnlyUser = User::factory()->create();
        $createOnlyUser->givePermissionTo(Permission::findByName('purchase-order-create', 'web'));

        $this->actingAs($createOnlyUser)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                ],
            ])
            ->assertRedirect(route('purchase-requests.purchase-orders.create', $purchaseRequest));

        $this->actingAs($createOnlyUser)
            ->get(route('purchase-requests.purchase-orders.create', $purchaseRequest))
            ->assertOk()
            ->assertSee('1 purchase order(s) generated successfully.');
    }

    public function test_purchase_order_create_page_loads_request_items_from_the_project_dropdown(): void
    {
        [$user, $purchaseRequest, $items] = $this->createApprovedRequest();

        $this->actingAs($user)
            ->get(route('purchase-orders.create'))
            ->assertOk()
            ->assertSee('Approved Project / Purchase Request')
            ->assertSee($purchaseRequest->project->project_name)
            ->assertDontSee($items[0]->material->name);

        $this->actingAs($user)
            ->get(route('purchase-orders.create', ['purchase_request' => $purchaseRequest->id]))
            ->assertOk()
            ->assertSee($items[0]->material->name)
            ->assertSee($items[1]->material->name)
            ->assertSee($items[2]->material->name)
            ->assertSee('name="items['.$items[0]->id.'][ordered_quantity]"', false)
            ->assertSee('name="items['.$items[0]->id.'][supplier_id]"', false)
            ->assertSee('name="items['.$items[0]->id.'][unit_price]"', false)
            ->assertSee('name="items['.$items[0]->id.'][discount]"', false)
            ->assertSee('name="items['.$items[0]->id.'][tax]"', false)
            ->assertSee(route('purchase-requests.purchase-orders.store', $purchaseRequest), false)
            ->assertSee('Supplier-Based Purchase Order Preview');
    }

    public function test_purchase_orders_cannot_be_created_from_pending_or_rejected_requests(): void
    {
        [$user, $purchaseRequest, $items, $supplierA] = $this->createApprovedRequest();
        $purchaseRequest->update(['status' => 'Pending']);

        $this->actingAs($user)
            ->get(route('purchase-requests.purchase-orders.create', $purchaseRequest))
            ->assertStatus(422);

        $requestData = [
            'order_date' => '2026-11-25',
            'items' => [
                $items[0]->id => [
                    'supplier_id' => $supplierA->id,
                    'ordered_quantity' => 1,
                    'unit_price' => 10,
                    'discount' => 0,
                    'tax' => 0,
                ],
            ],
        ];
        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), $requestData)
            ->assertStatus(422);

        $purchaseRequest->update(['status' => 'Rejected']);
        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), $requestData)
            ->assertStatus(422);

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_purchase_order_edits_keep_totals_correct_and_enforce_aggregate_quantities(): void
    {
        [$user, $purchaseRequest, $items, $supplierA] = $this->createApprovedRequest();

        $this->actingAs($user)
            ->post(route('purchase-orders.store'), [
                'purchase_request_id' => $purchaseRequest->id,
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'purchase_request_item_id' => $items[0]->id,
                        'material_id' => $items[0]->material_id,
                        'ordered_quantity' => 4,
                        'unit_price' => 10,
                    ],
                    [
                        'purchase_request_item_id' => $items[0]->id,
                        'material_id' => $items[0]->material_id,
                        'ordered_quantity' => 4,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertRedirect();

        $purchaseOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->firstOrFail();
        $orderItems = $purchaseOrder->items()->orderBy('id')->get();
        $this->assertCount(2, $orderItems);

        $this->actingAs($user)
            ->put(route('purchase-orders.update', $purchaseOrder), [
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'id' => $orderItems[0]->id,
                        'ordered_quantity' => 3,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertRedirect(route('purchase-orders.show', $purchaseOrder));

        $purchaseOrder->refresh();
        $this->assertEquals(70, (float) $purchaseOrder->grand_total);
        $this->assertEquals(3, (float) $orderItems[0]->fresh()->ordered_quantity);
        $this->assertEquals(4, (float) $orderItems[1]->fresh()->ordered_quantity);

        $this->actingAs($user)
            ->put(route('purchase-orders.update', $purchaseOrder), [
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'id' => $orderItems[0]->id,
                        'ordered_quantity' => 8,
                        'unit_price' => 10,
                    ],
                    [
                        'id' => $orderItems[1]->id,
                        'ordered_quantity' => 8,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertStatus(422);

        $this->actingAs($user)
            ->put(route('purchase-orders.update', $purchaseOrder), [
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'id' => $orderItems[0]->id,
                        'ordered_quantity' => 2,
                        'unit_price' => 10,
                    ],
                    [
                        'id' => $orderItems[0]->id,
                        'ordered_quantity' => 3,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertSessionHasErrors();

        $purchaseOrder->refresh();
        $this->assertEquals(70, (float) $purchaseOrder->grand_total);
        $this->assertEquals(3, (float) $orderItems[0]->fresh()->ordered_quantity);
        $this->assertEquals(4, (float) $orderItems[1]->fresh()->ordered_quantity);
    }

    public function test_legacy_purchase_order_routes_reject_inactive_suppliers(): void
    {
        [$user, $purchaseRequest, $items, $supplierA] = $this->createApprovedRequest();

        $this->actingAs($user)
            ->post(route('purchase-orders.store'), [
                'purchase_request_id' => $purchaseRequest->id,
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'purchase_request_item_id' => $items[0]->id,
                        'material_id' => $items[0]->material_id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertRedirect();

        $purchaseOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->firstOrFail();
        $orderItem = $purchaseOrder->items()->firstOrFail();
        $supplierA->update(['status' => 0]);

        $this->actingAs($user)
            ->post(route('purchase-orders.store'), [
                'purchase_request_id' => $purchaseRequest->id,
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'purchase_request_item_id' => $items[1]->id,
                        'material_id' => $items[1]->material_id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertSessionHasErrors('supplier_id');

        $this->actingAs($user)
            ->put(route('purchase-orders.update', $purchaseOrder), [
                'supplier_id' => $supplierA->id,
                'order_date' => '2026-11-25',
                'discount' => 0,
                'tax' => 0,
                'items' => [
                    [
                        'id' => $orderItem->id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertSessionHasErrors('supplier_id');

        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'supplier_id' => $supplierA->id,
            'grand_total' => 10,
        ]);
    }

    public function test_supplier_group_generation_rolls_back_all_orders_on_a_later_group_failure(): void
    {
        [$user, $purchaseRequest, $items, $supplierA, $supplierB] = $this->createApprovedRequest();

        $this->actingAs($user)
            ->post(route('purchase-requests.purchase-orders.store', $purchaseRequest), [
                'order_date' => '2026-11-25',
                'items' => [
                    $items[0]->id => [
                        'supplier_id' => $supplierA->id,
                        'ordered_quantity' => 1,
                        'unit_price' => 10,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                    $items[1]->id => [
                        'supplier_id' => $supplierB->id,
                        'ordered_quantity' => 6,
                        'unit_price' => 9000000000000,
                        'discount' => 0,
                        'tax' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors("items.{$items[1]->id}.unit_price");

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_items', 0);
    }

    private function createApprovedRequest(): array
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $client = Client::create([
            'name' => 'Supplier PO Test Client',
            'phone' => '1234567890',
        ]);
        $project = Projects::create([
            'client_id' => $client->id,
            'project_manager_id' => $user->id,
            'project_code' => 'SUPPLIER-PO-TEST',
            'project_name' => 'Supplier PO Test Project',
            'site_address' => 'Test address',
            'city' => 'Test city',
            'start_date' => '2026-01-01',
            'expected_end_date' => '2027-01-01',
            'estimated_budget' => 100000,
            'status' => 1,
        ]);
        $category = MaterialCategory::create(['name' => 'Supplier PO Test Materials']);
        $materials = collect([
            ['code' => 'MAT-SUP-PO-1', 'name' => 'Cement', 'unit' => 'bag'],
            ['code' => 'MAT-SUP-PO-2', 'name' => 'Rod', 'unit' => 'kg'],
            ['code' => 'MAT-SUP-PO-3', 'name' => 'Sand', 'unit' => 'cft'],
        ])->map(fn ($material) => Material::create([
            'material_code' => $material['code'],
            'name' => $material['name'],
            'category_id' => $category->id,
            'unit' => $material['unit'],
        ]));
        $supplierA = Supplier::create([
            'supplier_code' => 'SUP-PO-TEST-A',
            'company_name' => 'Supplier A',
            'phone' => '1234567890',
        ]);
        $supplierB = Supplier::create([
            'supplier_code' => 'SUP-PO-TEST-B',
            'company_name' => 'Supplier B',
            'phone' => '1234567890',
        ]);
        $purchaseRequest = PurchaseRequest::create([
            'request_number' => 'PR-SUPPLIER-PO-TEST',
            'project_id' => $project->id,
            'requester_id' => $user->id,
            'approved_by' => $user->id,
            'purpose' => 'Supplier-based PO generation test',
            'status' => 'Partially Approved',
            'approved_at' => now(),
        ]);
        $approvedQuantities = [10, 6, 8];
        $items = $materials->values()->map(function ($material, $index) use ($purchaseRequest, $approvedQuantities) {
            return PurchaseRequestItem::create([
                'purchase_request_id' => $purchaseRequest->id,
                'material_id' => $material->id,
                'requested_quantity' => $approvedQuantities[$index] + 2,
                'approved_quantity' => $approvedQuantities[$index],
                'required_date' => '2026-12-01',
            ]);
        })->all();

        return [$user, $purchaseRequest, $items, $supplierA, $supplierB];
    }
}
