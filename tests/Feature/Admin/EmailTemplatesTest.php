<?php

namespace Tests\Feature\Admin;

use App\Models\AdminNotification;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplateService;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_template_routes_require_authentication(): void
    {
        $template = EmailTemplate::create($this->data());

        $this->get(route('admin.email-templates.index'))->assertRedirect(route('login'));
        $this->get(route('admin.email-templates.create'))->assertRedirect(route('login'));
        $this->get(route('admin.email-templates.show', $template))->assertRedirect(route('login'));
        $this->get(route('admin.email-templates.preview', $template))->assertRedirect(route('login'));
        $this->post(route('admin.email-templates.store'), $this->data())->assertRedirect(route('login'));
    }

    public function test_default_email_template_seeder_is_idempotent(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        $this->seed(EmailTemplateSeeder::class);

        $this->assertSame(8, EmailTemplate::count());
        $this->assertDatabaseHas('email_templates', [
            'slug' => 'contact-reply',
            'type' => 'contact_reply',
            'is_default' => true,
            'status' => true,
        ]);
        $this->assertDatabaseHas('email_templates', [
            'slug' => 'maintenance-alert',
            'subject' => 'Maintenance mode status changed',
        ]);
        $this->assertDatabaseHas('email_templates', [
            'slug' => 'contact-admin-alert',
            'type' => 'admin_alert',
        ]);
        $this->assertDatabaseHas('email_templates', [
            'slug' => 'lead-admin-alert',
            'type' => 'admin_alert',
        ]);
    }

    public function test_admin_can_create_filter_view_preview_update_toggle_and_delete_templates(): void
    {
        $admin = User::factory()->create();
        EmailTemplate::create($this->data([
            'name' => 'Lead Reply',
            'slug' => 'lead-reply',
            'type' => 'lead_reply',
            'subject' => 'Lead subject',
        ]));

        $this->actingAs($admin)->get(route('admin.email-templates.create'))
            ->assertOk()
            ->assertSee('Variable Helper');

        $response = $this->actingAs($admin)->post(route('admin.email-templates.store'), $this->data([
            'name' => 'Customer Reply',
            'slug' => '',
            'subject' => 'Hello {name}',
            'body' => "Hello {name},\nThanks from {site_name}.",
            'available_variables' => "name\nsite_name",
        ]));

        $template = EmailTemplate::where('slug', 'customer-reply')->firstOrFail();
        $response->assertRedirect(route('admin.email-templates.show', $template));
        $this->assertSame(['name', 'site_name'], $template->available_variables);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create',
            'module' => 'email_templates',
            'model_id' => $template->id,
        ]);

        $this->actingAs($admin)->get(route('admin.email-templates.index', [
            'search' => 'Customer',
            'type' => 'custom',
            'status' => 'active',
        ]))->assertOk()
            ->assertSee('Customer Reply')
            ->assertDontSee('Lead subject');

        $this->actingAs($admin)->get(route('admin.email-templates.preview', $template))
            ->assertOk()
            ->assertSee('Hello Asha Patel')
            ->assertSee('Sample Data');
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'preview',
            'module' => 'email_templates',
            'model_id' => $template->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.email-templates.toggle-status', $template))
            ->assertSessionHasNoErrors();
        $this->assertFalse($template->refresh()->status);

        $this->actingAs($admin)->put(route('admin.email-templates.update', $template), $this->data([
            'name' => 'Customer Reply Updated',
            'slug' => 'customer-reply',
            'subject' => 'Updated subject',
            'status' => '1',
            'is_default' => '0',
        ]))->assertRedirect(route('admin.email-templates.show', $template));
        $this->assertSame('Customer Reply Updated', $template->refresh()->name);

        $this->actingAs($admin)->delete(route('admin.email-templates.destroy', $template))
            ->assertRedirect(route('admin.email-templates.index'));
        $this->assertSoftDeleted($template);
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Email Template Deleted',
            'module' => 'email_templates',
        ]);
    }

    public function test_default_template_update_notifies_and_default_delete_is_prevented(): void
    {
        $admin = User::factory()->create();
        $template = EmailTemplate::create($this->data([
            'name' => 'Contact Reply',
            'slug' => 'contact-reply',
            'type' => 'contact_reply',
            'is_default' => true,
        ]));

        $this->actingAs($admin)->put(route('admin.email-templates.update', $template), $this->data([
            'name' => 'Contact Reply',
            'slug' => 'contact-reply',
            'type' => 'contact_reply',
            'subject' => 'Changed default subject',
            'is_default' => '1',
        ]))->assertRedirect(route('admin.email-templates.show', $template));

        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Default Email Template Updated',
            'module' => 'email_templates',
        ]);

        $this->actingAs($admin)->delete(route('admin.email-templates.destroy', $template))
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted($template);
        $this->assertSame(1, AdminNotification::where('title', 'Default Email Template Updated')->count());
    }

    public function test_template_validation_and_render_service_work(): void
    {
        $admin = User::factory()->create();
        $existing = EmailTemplate::create($this->data(['slug' => 'existing-template']));

        $this->actingAs($admin)
            ->from(route('admin.email-templates.create'))
            ->post(route('admin.email-templates.store'), [
                'name' => '',
                'slug' => $existing->slug,
                'type' => 'invalid',
                'body' => '<script>alert(1)</script>',
                'available_variables' => "valid_name\nbad variable",
            ])
            ->assertRedirect(route('admin.email-templates.create'))
            ->assertSessionHasErrors(['name', 'slug', 'type', 'body', 'available_variables.1']);

        $template = EmailTemplate::create($this->data([
            'slug' => 'service-template',
            'type' => 'lead_reply',
            'subject' => 'Hello {name} from {site_name}',
            'body' => 'Lead status: {lead_status}. Missing {unknown} stays.',
        ]));

        $service = app(EmailTemplateService::class);
        $this->assertSame($template->id, $service->getTemplateBySlug('service-template')->id);
        $this->assertSame($template->id, $service->getTemplateByType('lead_reply')->id);
        $this->assertSame('Hello Mira from CMS Website', $service->renderSubject($template, [
            'name' => 'Mira',
            'site_name' => 'CMS Website',
        ]));
        $this->assertSame('Lead status: Warm. Missing {unknown} stays.', $service->renderBody($template, [
            'lead_status' => 'Warm',
        ]));
    }

    /** @return array<string, mixed> */
    private function data(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'subject' => 'Hello {name}',
            'type' => 'custom',
            'body' => "Hello {name},\nThis is a template from {site_name}.",
            'available_variables' => ['name', 'site_name'],
            'status' => '1',
            'is_default' => '0',
        ], $overrides);
    }
}
