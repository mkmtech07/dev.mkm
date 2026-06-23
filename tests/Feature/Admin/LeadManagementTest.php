<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_admin_routes_require_authentication(): void
    {
        $lead = Lead::create($this->validData());

        $this->get(route('admin.leads.index'))->assertRedirect(route('login'));
        $this->get(route('admin.leads.show', $lead))->assertRedirect(route('login'));
    }

    public function test_admin_can_manage_filter_and_update_leads(): void
    {
        $admin = User::factory()->create();
        $service = Service::create([
            'title' => 'Consulting', 'slug' => 'consulting', 'status' => true, 'sort_order' => 0,
        ]);

        $this->actingAs($admin)->get(route('admin.leads.create'))->assertOk()->assertSee('Contact information');

        $this->actingAs($admin)->post(route('admin.leads.store'), $this->validData([
            'name' => 'Asha Patel', 'service_id' => $service->id, 'assigned_to' => $admin->id,
        ]))->assertRedirect();

        $lead = Lead::firstOrFail();

        $this->actingAs($admin)->get(route('admin.leads.show', $lead))->assertOk()->assertSee('Activity timeline');
        $this->actingAs($admin)->get(route('admin.leads.edit', $lead))->assertOk()->assertSee('Pipeline');

        $this->actingAs($admin)
            ->get(route('admin.leads.index', ['search' => 'Asha', 'status' => 'new', 'priority' => 'high']))
            ->assertOk()
            ->assertSee('Asha Patel');

        $this->actingAs($admin)
            ->patch(route('admin.leads.status.update', $lead), ['status' => 'contacted'])
            ->assertSessionHasNoErrors();
        $this->assertSame('contacted', $lead->refresh()->status);

        $this->actingAs($admin)
            ->put(route('admin.leads.update', $lead), $this->validData(['name' => 'Asha Shah']))
            ->assertRedirect(route('admin.leads.show', $lead));
        $this->assertSame('Asha Shah', $lead->refresh()->name);

        $this->actingAs($admin)->delete(route('admin.leads.destroy', $lead))
            ->assertRedirect(route('admin.leads.index'));
        $this->assertSoftDeleted($lead);
    }

    public function test_admin_can_add_and_delete_timeline_notes(): void
    {
        $admin = User::factory()->create();
        $lead = Lead::create($this->validData());
        $followUp = now()->addDay()->format('Y-m-d H:i:s');

        $this->actingAs($admin)->post(route('admin.leads.notes.store', $lead), [
            'note' => 'Customer requested a callback.',
            'note_type' => 'call',
            'next_follow_up_date' => $followUp,
        ])->assertSessionHasNoErrors();

        $note = LeadNote::firstOrFail();
        $this->assertSame($admin->id, $note->user_id);
        $this->assertNotNull($lead->refresh()->follow_up_date);

        $this->actingAs($admin)->delete(route('admin.leads.notes.destroy', [$lead, $note]))
            ->assertSessionHasNoErrors();
        $this->assertSoftDeleted($note);
    }

    public function test_public_api_validates_and_stores_safe_lead_data(): void
    {
        $this->postJson(route('frontend.leads.store'), [
            'name' => 'Public Customer',
            'email' => 'customer@example.com',
            'message' => 'Please send a quote for your services.',
            'source' => 'quote_request',
            'preferred_contact_method' => 'email',
        ])->assertCreated()->assertJsonStructure(['message']);

        $this->assertDatabaseHas('leads', [
            'name' => 'Public Customer', 'source' => 'quote_request', 'status' => 'new',
        ]);

        $this->postJson(route('frontend.leads.store'), [
            'name' => '', 'message' => '', 'source' => 'manual',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'message', 'source']);

        $this->postJson(route('frontend.leads.store'), [
            'name' => 'Bot', 'message' => 'Spam', 'website' => 'https://spam.test',
        ])->assertUnprocessable()->assertJsonValidationErrors(['website']);
    }

    public function test_contact_message_can_be_converted_without_changing_original_module(): void
    {
        $admin = User::factory()->create();
        $message = ContactMessage::create([
            'name' => 'Contact Customer', 'email' => 'contact@example.com', 'message' => 'Call me.',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.contact-messages.convert-to-lead', $message));

        $lead = Lead::firstOrFail();
        $response->assertRedirect(route('admin.leads.show', $lead));
        $this->assertSame('contact_form', $lead->source);
        $this->assertTrue($message->refresh()->is_read);
    }

    /** @return array<string, mixed> */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Customer', 'email' => 'lead@example.com', 'phone' => '9999999999',
            'source' => 'manual', 'status' => 'new', 'priority' => 'high',
            'preferred_contact_method' => 'phone', 'status_active' => true,
        ], $overrides);
    }
}
