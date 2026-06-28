<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tenantAwareTables = [
        'website_settings',
        'theme_settings',
        'homepage_sections',
        'hero_sliders',
        'menus',
        'menu_items',
        'footer_settings',
        'footer_sections',
        'footer_links',
        'footer_social_links',
        'pages',
        'page_blocks',
        'services',
        'about_sections',
        'team_members',
        'galleries',
        'testimonials',
        'faqs',
        'blogs',
        'blog_categories',
        'seo_pages',
        'schema_markups',
        'media_files',
        'contact_messages',
        'leads',
        'newsletter_subscribers',
    ];

    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('status', 30)->default('active')->index();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamp('demo_expires_at')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->json('allowed_modules')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('tenant_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color', 20)->nullable();
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->mediumText('custom_css')->nullable();
            $table->mediumText('custom_js')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('locale', 20)->default('en');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        $defaultTenantId = DB::table('tenants')->insertGetId([
            'name' => 'Default Tenant',
            'slug' => 'default',
            'status' => 'active',
            'is_demo' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tenant_settings')->insert([
            'tenant_id' => $defaultTenantId,
            'timezone' => config('app.timezone', 'UTC'),
            'locale' => config('app.locale', 'en'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($this->tenantAwareTables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            });
        }

        foreach ($this->tenantAwareTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                DB::table($tableName)->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tenantAwareTables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            try {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropIndex(['tenant_id']);
                });
            } catch (\Throwable) {
                //
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('tenant_id');
            });
        }

        Schema::dropIfExists('tenant_settings');
        Schema::dropIfExists('tenants');
    }
};
