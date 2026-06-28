<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /** @var array<string, array<int, string>> */
    private const MODULES = [
        'Dashboard' => ['dashboard.view'],
        'Tenants' => ['tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete', 'tenants.settings', 'tenants.switch'],
        'Pages' => ['pages.view', 'pages.create', 'pages.edit', 'pages.delete'],
        'Blog' => ['blog.view', 'blog.create', 'blog.edit', 'blog.delete'],
        'Blog Categories' => ['blog_categories.view', 'blog_categories.create', 'blog_categories.edit', 'blog_categories.delete'],
        'Services' => ['services.view', 'services.create', 'services.edit', 'services.delete'],
        'About' => ['about.view', 'about.create', 'about.edit', 'about.delete'],
        'Testimonials' => ['testimonials.view', 'testimonials.create', 'testimonials.edit', 'testimonials.delete'],
        'Team Members' => ['team_members.view', 'team_members.create', 'team_members.edit', 'team_members.delete'],
        'Gallery' => ['gallery.view', 'gallery.create', 'gallery.edit', 'gallery.delete'],
        'FAQ' => ['faq.view', 'faq.create', 'faq.edit', 'faq.delete'],
        'Menus' => ['menus.view', 'menus.create', 'menus.edit', 'menus.delete'],
        'Footer' => ['footer.view', 'footer.edit'],
        'Website Settings' => ['website_settings.view', 'website_settings.edit'],
        'Theme Settings' => ['theme_settings.view', 'theme_settings.edit'],
        'Maintenance' => ['maintenance.view', 'maintenance.edit'],
        'Homepage Sections' => ['homepage_sections.view', 'homepage_sections.create', 'homepage_sections.edit', 'homepage_sections.delete'],
        'Page Blocks' => ['page_blocks.view', 'page_blocks.create', 'page_blocks.edit', 'page_blocks.delete'],
        'Hero Sliders' => ['hero_sliders.view', 'hero_sliders.create', 'hero_sliders.edit', 'hero_sliders.delete'],
        'Media Library' => ['media_library.view', 'media_library.create', 'media_library.edit', 'media_library.delete'],
        'Media Picker' => ['media_picker.use'],
        'SEO' => ['seo.view', 'seo.create', 'seo.edit', 'seo.delete'],
        'Leads' => ['leads.view', 'leads.create', 'leads.edit', 'leads.delete'],
        'Newsletter' => ['newsletter.view', 'newsletter.create', 'newsletter.edit', 'newsletter.delete'],
        'Contact Messages' => ['contact_messages.view', 'contact_messages.delete'],
        'Backups' => ['backups.view', 'backups.create', 'backups.download', 'backups.delete'],
        'Notifications' => ['notifications.view', 'notifications.mark_read', 'notifications.delete'],
        'Email Templates' => ['email_templates.view', 'email_templates.create', 'email_templates.edit', 'email_templates.delete', 'email_templates.preview'],
        'Email Automation' => ['email_automation.view', 'email_automation.edit'],
        'Mail Settings' => ['mail_settings.view', 'mail_settings.edit', 'mail_settings.test'],
        'Mail Logs' => ['mail_logs.view', 'mail_logs.delete'],
        'Activity Logs' => ['activity_logs.view'],
        'Roles' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
        'Permissions' => ['permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete'],
        'User Roles' => ['users.roles.manage'],
    ];

    public function run(): void
    {
        $permissions = collect();
        foreach (self::MODULES as $module => $slugs) {
            foreach ($slugs as $slug) {
                $permission = Permission::withTrashed()->firstOrNew(['slug' => $slug]);
                $permission->fill([
                    'name' => Str::headline(str_replace('.', ' ', $slug)),
                    'module' => $module,
                    'description' => 'Allows '.strtolower(Str::headline(str_replace('.', ' ', $slug))).'.',
                    'status' => true,
                ]);
                $permission->deleted_at = null;
                $permission->save();
                $permissions->push($permission);
            }
        }

        $roles = [
            Role::SUPER_ADMIN => ['name' => 'Super Admin', 'description' => 'Unrestricted CMS access.'],
            'admin' => ['name' => 'Admin', 'description' => 'Full operational CMS access without access-control administration.'],
            'editor' => ['name' => 'Editor', 'description' => 'Website content and publishing access.'],
            'manager' => ['name' => 'Manager', 'description' => 'Customer enquiries, newsletters, and reporting access.'],
            'staff' => ['name' => 'Staff', 'description' => 'Limited day-to-day CMS access.'],
        ];

        foreach ($roles as $slug => $attributes) {
            $role = Role::withTrashed()->firstOrNew(['slug' => $slug]);
            $role->fill([...$attributes, 'status' => true]);
            $role->deleted_at = null;
            $role->save();

            $allowed = match ($slug) {
                Role::SUPER_ADMIN => $permissions,
                'admin' => $permissions->reject(fn (Permission $permission) => in_array($permission->module, ['Roles', 'Permissions', 'User Roles'], true)),
                'editor' => $permissions->filter(fn (Permission $permission) => in_array($permission->module, [
                    'Dashboard', 'Pages', 'Blog', 'Blog Categories', 'Services', 'About', 'Testimonials',
                    'Team Members', 'Gallery', 'FAQ', 'Menus', 'Footer', 'Homepage Sections',
                    'Page Blocks', 'Hero Sliders', 'Media Library', 'Media Picker', 'SEO', 'Maintenance', 'Email Templates',
                ], true)),
                'manager' => $permissions->filter(fn (Permission $permission) => in_array($permission->module, [
                    'Dashboard', 'Leads', 'Newsletter', 'Contact Messages', 'Notifications', 'Activity Logs',
                ], true)),
                default => $permissions->filter(fn (Permission $permission) => in_array($permission->slug, [
                    'dashboard.view', 'leads.view', 'leads.create', 'leads.edit', 'contact_messages.view',
                ], true)),
            };

            $role->permissions()->sync($allowed->pluck('id')->all());
        }

        $mainAdmin = User::query()->oldest('id')->first();
        $superAdminRole = Role::query()->where('slug', Role::SUPER_ADMIN)->first();
        if ($mainAdmin && $superAdminRole) {
            $mainAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}
