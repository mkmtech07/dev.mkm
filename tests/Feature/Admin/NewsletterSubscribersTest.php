<?php

namespace Tests\Feature\Admin;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscribersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication(): void
    {
        $subscriber = NewsletterSubscriber::create($this->data());

        $this->get(route('admin.newsletter-subscribers.index'))->assertRedirect(route('login'));
        $this->get(route('admin.newsletter-subscribers.show', $subscriber))->assertRedirect(route('login'));
        $this->get(route('admin.newsletter-subscribers.export'))->assertRedirect(route('login'));
    }

    public function test_admin_can_create_render_filter_update_status_and_delete_subscribers(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->get(route('admin.newsletter-subscribers.create'))
            ->assertOk()->assertSee('Subscriber information');

        $this->actingAs($admin)->post(route('admin.newsletter-subscribers.store'), $this->data([
            'name' => 'Asha Patel', 'email' => 'asha@example.com',
        ]))->assertRedirect();
        $subscriber = NewsletterSubscriber::firstOrFail();

        $this->assertNotNull($subscriber->unsubscribe_token);
        $this->assertNotNull($subscriber->subscribed_at);
        $this->actingAs($admin)->get(route('admin.newsletter-subscribers.show', $subscriber))
            ->assertOk()->assertSee('Asha Patel')->assertSee('Submission reference');
        $this->actingAs($admin)->get(route('admin.newsletter-subscribers.edit', $subscriber))
            ->assertOk()->assertSee('asha@example.com');
        $this->actingAs($admin)->get(route('admin.newsletter-subscribers.index', [
            'search' => 'Asha', 'source' => 'manual', 'status' => 'subscribed',
        ]))->assertOk()->assertSee('asha@example.com');

        $this->actingAs($admin)->patch(route('admin.newsletter-subscribers.status.update', $subscriber), [
            'status' => 'unsubscribed',
        ])->assertSessionHasNoErrors();
        $this->assertSame('unsubscribed', $subscriber->refresh()->status);
        $this->assertNotNull($subscriber->unsubscribed_at);

        $this->actingAs($admin)->put(route('admin.newsletter-subscribers.update', $subscriber), $this->data([
            'name' => 'Asha Shah', 'email' => 'asha@example.com', 'status' => 'subscribed',
        ]))->assertRedirect(route('admin.newsletter-subscribers.show', $subscriber));
        $this->assertSame('Asha Shah', $subscriber->refresh()->name);
        $this->assertNull($subscriber->unsubscribed_at);

        $this->actingAs($admin)->delete(route('admin.newsletter-subscribers.destroy', $subscriber))
            ->assertRedirect(route('admin.newsletter-subscribers.index'));
        $this->assertSoftDeleted($subscriber);
    }

    public function test_filtered_csv_export_contains_only_matching_safe_rows(): void
    {
        $admin = User::factory()->create();
        NewsletterSubscriber::create($this->data([
            'name' => '=Formula', 'email' => 'match@example.com', 'source' => 'footer',
        ]));
        NewsletterSubscriber::create($this->data([
            'name' => 'Other', 'email' => 'other@example.com', 'source' => 'manual',
        ]));

        $response = $this->actingAs($admin)->get(route('admin.newsletter-subscribers.export', [
            'source' => 'footer',
        ]));

        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('match@example.com', $content);
        $this->assertStringNotContainsString('other@example.com', $content);
        $this->assertStringContainsString("'=Formula", $content);
    }

    public function test_public_subscribe_handles_duplicates_resubscription_and_safe_unsubscribe(): void
    {
        $this->postJson(route('frontend.newsletter.subscribe'), [
            'name' => 'Public Reader', 'email' => 'reader@example.com', 'source' => 'footer',
        ])->assertCreated()->assertJsonStructure(['message']);
        $subscriber = NewsletterSubscriber::firstOrFail();
        $this->assertSame('subscribed', $subscriber->status);
        $this->assertNotNull($subscriber->ip_address);

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'READER@example.com', 'source' => 'footer',
        ])->assertCreated()->assertJsonPath('message', 'This email address is already subscribed.');
        $this->assertSame(1, NewsletterSubscriber::count());

        $this->postJson(route('frontend.newsletter.unsubscribe'), [
            'email' => 'reader@example.com', 'unsubscribe_token' => str_repeat('x', 64),
        ])->assertUnprocessable();
        $this->assertSame('subscribed', $subscriber->refresh()->status);

        $this->postJson(route('frontend.newsletter.unsubscribe'), [
            'email' => 'reader@example.com', 'unsubscribe_token' => $subscriber->unsubscribe_token,
        ])->assertOk()->assertJsonStructure(['message']);
        $this->assertSame('unsubscribed', $subscriber->refresh()->status);

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'reader@example.com', 'source' => 'blog',
        ])->assertCreated();
        $this->assertSame('subscribed', $subscriber->refresh()->status);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertSame(1, NewsletterSubscriber::count());
    }

    public function test_public_subscribe_validates_input_and_honeypot(): void
    {
        $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'not-an-email', 'source' => 'manual',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'source']);

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'bot@example.com', 'website' => 'https://spam.test',
        ])->assertUnprocessable()->assertJsonValidationErrors(['website']);
    }

    /** @return array<string, mixed> */
    private function data(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Subscriber', 'email' => 'subscriber@example.com', 'phone' => '9999999999',
            'source' => 'manual', 'status' => 'subscribed', 'notes' => 'Internal note', 'status_active' => true,
        ], $overrides);
    }
}
