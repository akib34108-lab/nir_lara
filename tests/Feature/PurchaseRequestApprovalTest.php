<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Projects;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_different_authorized_user_can_approve_a_pending_request(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $requester = User::factory()->create();
        $requester->assignRole('Super Admin');
        $approver = User::factory()->create();
        $approver->assignRole('Project Manager');

        $client = Client::create([
            'name' => 'Purchase Request Test Client',
            'phone' => '1234567890',
        ]);
        $project = Projects::create([
            'client_id' => $client->id,
            'project_manager_id' => $approver->id,
            'project_code' => 'PR-APPROVAL-TEST',
            'project_name' => 'Purchase Request Test Project',
            'site_address' => 'Test address',
            'city' => 'Test city',
            'start_date' => '2026-01-01',
            'expected_end_date' => '2027-01-01',
            'estimated_budget' => 100000,
            'status' => 1,
        ]);
        $category = MaterialCategory::create(['name' => 'Test Materials']);
        $material = Material::create([
            'material_code' => 'MAT-APPROVAL-TEST',
            'name' => 'Test Material',
            'category_id' => $category->id,
            'unit' => 'piece',
        ]);
        $purchaseRequest = PurchaseRequest::create([
            'request_number' => 'PR-2026-APPROVAL',
            'project_id' => $project->id,
            'requester_id' => $requester->id,
            'purpose' => 'Test approval flow',
            'status' => 'Pending',
        ]);
        $item = PurchaseRequestItem::create([
            'purchase_request_id' => $purchaseRequest->id,
            'material_id' => $material->id,
            'requested_quantity' => 5,
            'approved_quantity' => 0,
            'required_date' => '2026-12-01',
        ]);

        $this->actingAs($requester)
            ->get(route('purchase-requests.show', $purchaseRequest))
            ->assertOk()
            ->assertSee('Another user with approval permission must review it.')
            ->assertDontSee('Approve / Partially Approve');

        $this->actingAs($requester)
            ->post(route('purchase-requests.approve', $purchaseRequest), [
                'approved_quantity' => [$item->id => 5],
            ])
            ->assertForbidden();

        $this->actingAs($approver)
            ->get(route('purchase-requests.show', $purchaseRequest))
            ->assertOk()
            ->assertSee('Approve / Partially Approve');

        $this->actingAs($approver)
            ->post(route('purchase-requests.approve', $purchaseRequest), [
                'approved_quantity' => [$item->id => 5],
            ])
            ->assertRedirect(route('purchase-requests.show', $purchaseRequest));

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $purchaseRequest->id,
            'status' => 'Approved',
            'approved_by' => $approver->id,
        ]);
    }
}
