<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_form_can_create_a_message(): void
    {
        $response = $this->postJson(route('frontend.contact-messages.store'), [
            'name' => 'Priya Shah',
            'phone' => '+91 98765 43210',
            'email' => 'priya@example.com',
            'subject' => 'Website enquiry',
            'message' => 'Please share more information about your services.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'source' => 'contact-page',
            'is_read' => false,
        ]);
    }

    public function test_public_contact_form_validates_input(): void
    {
        $this->postJson(route('frontend.contact-messages.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_admin_contact_message_routes_require_authentication(): void
    {
        $contactMessage = ContactMessage::create([
            'name' => 'Guest User',
            'message' => 'A private enquiry.',
        ]);

        $this->get(route('admin.contact-messages.index'))
            ->assertRedirect(route('login'));
        $this->get(route('admin.contact-messages.show', $contactMessage))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_filter_search_and_manage_contact_messages(): void
    {
        $admin = User::factory()->create();
        $matchingMessage = ContactMessage::create([
            'name' => 'Anil Kumar',
            'email' => 'anil@example.com',
            'subject' => 'Billing question',
            'message' => 'I need help with billing.',
        ]);
        ContactMessage::create([
            'name' => 'Read Sender',
            'subject' => 'Other question',
            'message' => 'Already handled.',
            'is_read' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.index', [
                'search' => 'Billing',
                'status' => 'unread',
            ]))
            ->assertOk()
            ->assertSee('Anil Kumar')
            ->assertDontSee('Read Sender');

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $matchingMessage))
            ->assertOk()
            ->assertSee('I need help with billing.');

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.toggle-read', $matchingMessage))
            ->assertSessionHasNoErrors();
        $this->assertTrue($matchingMessage->refresh()->is_read);

        $this->actingAs($admin)
            ->put(route('admin.contact-messages.update', $matchingMessage), [
                'notes' => 'Call the customer tomorrow.',
            ])
            ->assertSessionHasNoErrors();
        $this->assertSame('Call the customer tomorrow.', $matchingMessage->refresh()->notes);

        $this->actingAs($admin)
            ->delete(route('admin.contact-messages.destroy', $matchingMessage))
            ->assertRedirect(route('admin.contact-messages.index'));
        $this->assertSoftDeleted($matchingMessage);
    }
}
