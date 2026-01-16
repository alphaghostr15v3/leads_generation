<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_access_leads_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.leads.index'));
        $response->assertStatus(200);
        $response->assertSee('Leads Management');
    }

    public function test_admin_can_access_lead_edit_page()
    {
        $lead = Lead::factory()->create();
        $response = $this->actingAs($this->admin)->get(route('admin.leads.edit', $lead));
        $response->assertStatus(200);
        $response->assertSee('Edit Lead');
        $response->assertSee($lead->name);
    }

    public function test_admin_can_bulk_delete_leads()
    {
        $leads = Lead::factory()->count(3)->create();
        $ids = $leads->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)->post(route('admin.leads.bulk-delete'), [
            'ids' => $ids
        ]);

        $response->assertRedirect(route('admin.leads.index'));
        $response->assertSessionHas('success', '3 leads deleted successfully!');
        
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('leads', ['id' => $id]);
        }
    }
}
