<?php

namespace Tests\Feature\Admin;

use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_tenant_exists_and_new_content_receives_it(): void
    {
        $default = Tenant::where('slug', 'default')->firstOrFail();

        $setting = WebsiteSetting::create([
            'site_name' => 'Default Website',
            'status' => true,
        ]);

        $this->assertSame('Default Tenant', $default->name);
        $this->assertSame($default->id, $setting->tenant_id);
        $this->assertDatabaseHas('tenant_settings', ['tenant_id' => $default->id]);
    }

    public function test_public_requests_resolve_tenant_from_subdomain_and_scope_content(): void
    {
        $default = Tenant::where('slug', 'default')->firstOrFail();
        WebsiteSetting::create([
            'tenant_id' => $default->id,
            'site_name' => 'Default Site',
            'status' => true,
        ]);

        $tenant = Tenant::create([
            'name' => 'Client Demo',
            'slug' => 'client-demo',
            'subdomain' => 'client',
            'status' => Tenant::STATUS_ACTIVE,
            'is_demo' => true,
        ]);
        $tenant->setting()->create(['timezone' => 'UTC', 'locale' => 'en']);
        WebsiteSetting::create([
            'tenant_id' => $tenant->id,
            'site_name' => 'Client Site',
            'status' => true,
        ]);

        $this->getJson('/api/website-settings')
            ->assertOk()
            ->assertJsonPath('site_name', 'Default Site');

        $this->getJson('http://client.example.test/api/website-settings')
            ->assertOk()
            ->assertJsonPath('site_name', 'Client Site');
    }

    public function test_suspended_public_tenant_returns_unavailable_page(): void
    {
        Tenant::create([
            'name' => 'Paused Demo',
            'slug' => 'paused-demo',
            'subdomain' => 'paused',
            'status' => Tenant::STATUS_SUSPENDED,
            'is_demo' => true,
        ]);

        $this->get('http://paused.example.test/api/website-settings')
            ->assertStatus(503)
            ->assertSee('Demo unavailable');
    }

    public function test_admin_can_manage_and_switch_tenants(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.tenants.store'), [
                'name' => 'Acme Demo',
                'slug' => 'acme-demo',
                'subdomain' => 'acme',
                'custom_domain' => 'demo.acme.test',
                'status' => Tenant::STATUS_ACTIVE,
                'is_demo' => '1',
                'client_name' => 'Acme',
                'client_email' => 'client@acme.test',
                'client_phone' => '+1 555 0100',
                'allowed_modules' => ['pages', 'services'],
            ])
            ->assertSessionHasNoErrors();

        $tenant = Tenant::where('slug', 'acme-demo')->firstOrFail();
        $this->assertDatabaseHas('tenant_settings', ['tenant_id' => $tenant->id]);

        $this->actingAs($admin)
            ->post(route('admin.tenants.switch'), ['tenant_id' => $tenant->id])
            ->assertSessionHas(TenantManager::SESSION_KEY, $tenant->id)
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'title' => 'Client Service',
                'slug' => 'client-service',
                'short_description' => 'Tenant-specific service.',
                'description' => 'A service created after switching tenants.',
                'status' => '1',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHasNoErrors();

        $service = Service::withoutGlobalScope('tenant')->where('slug', 'client-service')->firstOrFail();
        $this->assertSame($tenant->id, $service->tenant_id);
    }
}
