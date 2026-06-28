<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') | {{ $websiteSettings->site_name ?: 'CMS Website' }}</title>

        @if ($websiteSettings->favicon)
            <link rel="icon" href="{{ asset($websiteSettings->favicon) }}">
        @endif

        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
        @stack('styles')
    </head>
    <body>
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                @if ($websiteSettings->logo)
                    <img
                        class="admin-brand-logo"
                        src="{{ asset($websiteSettings->logo) }}"
                        alt="{{ $websiteSettings->site_name ?: 'CMS Website' }} logo"
                    >
                @else
                    <span class="admin-brand-mark" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/>
                        </svg>
                    </span>
                @endif
                <span>{{ $websiteSettings->site_name ?: 'CMS Website' }}</span>
            </a>

            @php($adminUser = auth()->user())
            <nav class="nav flex-column pb-4">
                <div class="admin-nav-label">Main</div>

                @if($adminUser->hasPermission('dashboard.view'))
                <a class="nav-link {{ request()->routeIs('admin.dashboard', 'dashboard') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 12l9-9 9 9"/>
                        <path d="M5 10v11h14V10M9 21v-7h6v7"/>
                    </svg>
                    Dashboard
                </a>
                @endif

                @if($adminUser->hasPermission('notifications.view'))
                <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}"
                   href="{{ route('admin.notifications.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                        <path d="M10 21h4"/>
                    </svg>
                    Notifications
                </a>
                @endif

                @if($adminUser->hasPermission('tenants.view'))
                <a class="nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}"
                   href="{{ route('admin.tenants.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 4h16v16H4z"/>
                        <path d="M8 8h8M8 12h8M8 16h4"/>
                    </svg>
                    Client Demos
                </a>
                @endif

                @if($adminUser->hasPermission('email_templates.view'))
                <a class="nav-link {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}"
                   href="{{ route('admin.email-templates.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 4h16v16H4z"/>
                        <path d="m4 7 8 6 8-6M8 16h8"/>
                    </svg>
                    Email Templates
                </a>
                @endif

                @if($adminUser->hasPermission('mail_settings.view'))
                <a class="nav-link {{ request()->routeIs('admin.mail-settings.*') ? 'active' : '' }}"
                   href="{{ route('admin.mail-settings.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16v14H4z"/>
                        <path d="m4 7 8 6 8-6"/>
                        <path d="M8 17h8"/>
                    </svg>
                    Mail Settings
                </a>
                @endif

                @if($adminUser->hasPermission('email_automation.view'))
                <a class="nav-link {{ request()->routeIs('admin.email-automation.*') ? 'active' : '' }}"
                   href="{{ route('admin.email-automation.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16v14H4z"/>
                        <path d="m4 7 8 6 8-6"/>
                        <path d="M8 16h4M15 15l2 2 4-4"/>
                    </svg>
                    Email Automation
                </a>
                @endif

                @if($adminUser->hasPermission('mail_logs.view'))
                <a class="nav-link {{ request()->routeIs('admin.mail-logs.*') ? 'active' : '' }}"
                   href="{{ route('admin.mail-logs.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 3h9l3 3v15H6z"/>
                        <path d="M15 3v4h4M9 11h6M9 15h6M9 19h3"/>
                    </svg>
                    Mail Logs
                </a>
                @endif

                @if($adminUser->hasPermission('theme_settings.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.theme-settings.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.theme-settings.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M12 3a9 9 0 1 0 9 9c0-1.1-.9-2-2-2h-2.2a2 2 0 0 1-1.6-3.2l.7-.9A2 2 0 0 0 14.3 3z"/>
                        <circle cx="7.5" cy="10.5" r="1"/><circle cx="10" cy="6.5" r="1"/><circle cx="7.5" cy="15" r="1"/>
                    </svg>
                    Theme Customizer
                </a>
                @endif

                <div class="admin-nav-label">Website</div>

                @if($adminUser->hasPermission('website_settings.view'))
                <a class="nav-link {{ request()->routeIs('admin.settings.*', 'admin.website.settings.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.settings.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.6v-.2h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1z"/>
                    </svg>
                    Website Settings
                </a>
                @endif

                @if($adminUser->hasPermission('maintenance.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.maintenance.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.maintenance.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 7h16M7 7v10a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7"/>
                        <path d="M9 7V5a3 3 0 0 1 6 0v2M9 12h6"/>
                    </svg>
                    Maintenance Mode
                </a>
                @endif

                @if($adminUser->hasPermission('homepage_sections.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.homepage-sections.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.homepage-sections.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 9v12M13 13h4M13 17h4"/>
                    </svg>
                    Homepage Sections
                </a>
                @endif

                @if($adminUser->hasPermission('page_blocks.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.page-blocks.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.page-blocks.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M7 8h10M7 12h4M13 12h4M7 16h10"/>
                    </svg>
                    Page Blocks
                </a>
                @endif

                @if($adminUser->hasPermission('seo.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.seo.pages.*') ? 'active' : '' }}" href="{{ route('admin.website.seo.pages.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5M8 11h6M11 8v6"/></svg>
                    SEO Pages
                </a>
                <a class="nav-link {{ request()->routeIs('admin.website.seo.settings.*') ? 'active' : '' }}" href="{{ route('admin.website.seo.settings.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M7 12h10M9 18h6"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="12" cy="18" r="2"/></svg>
                    SEO Settings
                </a>
                <a class="nav-link {{ request()->routeIs('admin.website.seo.schema.*') ? 'active' : '' }}" href="{{ route('admin.website.seo.schema.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 3H5v18h3M16 3h3v18h-3M10 8l4 4-4 4"/></svg>
                    Schema Markup
                </a>
                @endif

                @if($adminUser->hasPermission('media_library.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.media-library.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.media-library.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="8.5" cy="9" r="1.5"/><path d="m3 16 5-4 4 3 3-2 6 4"/>
                    </svg>
                    Media Library
                </a>
                @endif

                @if($adminUser->hasPermission('hero_sliders.view'))
                <a class="nav-link {{ request()->routeIs('admin.hero-sliders.*') ? 'active' : '' }}"
                   href="{{ route('admin.hero-sliders.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="8.5" cy="9" r="1.5"/>
                        <path d="m3 16 5-4 4 3 3-2 6 4"/>
                    </svg>
                    Hero Sliders
                </a>
                @endif

                @if($adminUser->hasPermission('services.view'))
                <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}"
                   href="{{ route('admin.services.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h10"/>
                        <circle cx="18" cy="17" r="2"/>
                    </svg>
                    Services
                </a>
                @endif

                @if($adminUser->hasPermission('about.view'))
                <a class="nav-link {{ request()->routeIs('admin.about.*') ? 'active' : '' }}"
                   href="{{ route('admin.about.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>
                    About Us
                </a>
                @endif

                @if($adminUser->hasPermission('gallery.view'))
                <a class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}"
                   href="{{ route('admin.gallery.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="8.5" cy="9" r="1.5"/>
                        <path d="m3 16 5-4 4 3 3-2 6 4"/>
                    </svg>
                    Gallery
                </a>
                @endif

                @if($adminUser->hasPermission('testimonials.view'))
                <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}"
                   href="{{ route('admin.testimonials.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16v11H8l-4 4z"/>
                        <path d="M8 9h8M8 12h5"/>
                    </svg>
                    Testimonials
                </a>
                @endif

                @if($adminUser->hasPermission('team_members.view'))
                <a class="nav-link {{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"
                   href="{{ route('admin.team-members.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="9" cy="8" r="3"/>
                        <circle cx="17" cy="10" r="2"/>
                        <path d="M3 20a6 6 0 0 1 12 0M14 16a5 5 0 0 1 7 4"/>
                    </svg>
                    Team Members
                </a>
                @endif

                @if($adminUser->hasPermission('pages.view'))
                <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"
                   href="{{ route('admin.pages.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 2h8l4 4v16H6z"/>
                        <path d="M14 2v5h5M9 12h6M9 16h6"/>
                    </svg>
                    Pages
                </a>
                @endif

                @if($adminUser->hasPermission('menus.view'))
                <a class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}"
                   href="{{ route('admin.menus.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                        <circle cx="2" cy="6" r=".5" fill="currentColor"/>
                        <circle cx="2" cy="12" r=".5" fill="currentColor"/>
                        <circle cx="2" cy="18" r=".5" fill="currentColor"/>
                    </svg>
                    Menus
                </a>
                @endif

                @if($adminUser->hasPermission('footer.view'))
                <a class="nav-link {{ request()->routeIs('admin.website.footer.settings.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.footer.settings.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16v14H4zM4 15h16M8 19v-4"/>
                    </svg>
                    Footer Settings
                </a>

                <a class="nav-link {{ request()->routeIs('admin.website.footer.sections.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.footer.sections.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 4v16M8 10h13"/>
                    </svg>
                    Footer Sections
                </a>

                <a class="nav-link {{ request()->routeIs('admin.website.footer.links.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.footer.links.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1"/>
                    </svg>
                    Footer Links
                </a>

                <a class="nav-link {{ request()->routeIs('admin.website.footer.social.*') ? 'active' : '' }}"
                   href="{{ route('admin.website.footer.social.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.5 6.8-4M8.6 13.5l6.8 4"/>
                    </svg>
                    Footer Social Links
                </a>
                @endif

                @if($adminUser->hasPermission('faq.view'))
                <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}"
                   href="{{ route('admin.faqs.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.1 9a3 3 0 1 1 4.8 2.4c-1.1.8-1.9 1.3-1.9 2.6M12 18h.01"/>
                    </svg>
                    FAQs
                </a>
                @endif

                @if($adminUser->hasPermission('contact_messages.view'))
                <a class="nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"
                   href="{{ route('admin.contact-messages.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16v14H4z"/>
                        <path d="m4 7 8 6 8-6"/>
                    </svg>
                    Contact Messages
                </a>
                @endif

                @if($adminUser->hasPermission('leads.view'))
                <a class="nav-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}"
                   href="{{ route('admin.leads.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 4h16v16H4zM8 8h8M8 12h5"/><path d="m14 16 2 2 4-4"/>
                    </svg>
                    Leads / Enquiries
                </a>
                @endif

                @if($adminUser->hasPermission('newsletter.view'))
                <a class="nav-link {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'active' : '' }}"
                   href="{{ route('admin.newsletter-subscribers.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 5h18v14H3zM3 7l9 7 9-7"/><path d="M7 3h10"/>
                    </svg>
                    Newsletter Subscribers
                </a>
                @endif

                @if($adminUser->hasPermission('backups.view'))
                <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}"
                   href="{{ route('admin.backups.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>
                    </svg>
                    Backups
                </a>
                @endif

                @if($adminUser->hasPermission('activity_logs.view'))
                <a class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"
                   href="{{ route('admin.activity-logs.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2M5 4l-2 2M19 4l2 2"/>
                    </svg>
                    Activity Logs
                </a>
                @endif

                @if($adminUser->hasAnyPermission(['roles.view','permissions.view','users.roles.manage']))
                <div class="admin-nav-label">Access Control</div>
                @if($adminUser->hasPermission('roles.view'))
                <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 21v-2a4 4 0 0 1 4-4h4M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M16 11l2 2 4-4"/></svg>
                    Roles
                </a>
                @endif
                @if($adminUser->hasPermission('permissions.view'))
                <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8" cy="15" r="4"/><path d="m11 12 8-8 2 2-2 2 1.5 1.5-2 2L17 10l-3 3"/></svg>
                    Permissions
                </a>
                @endif
                @if($adminUser->hasPermission('users.roles.manage'))
                <a class="nav-link {{ request()->routeIs('admin.user-roles.*') ? 'active' : '' }}" href="{{ route('admin.user-roles.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="7" r="4"/><path d="M3 21a6 6 0 0 1 12 0M16 11h6M19 8v6"/></svg>
                    User Roles
                </a>
                @endif
                @endif

                @if($adminUser->hasPermission('blog_categories.view'))
                <a class="nav-link {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}"
                   href="{{ route('admin.blog-categories.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 5h7l2 2h9v12H3z"/>
                        <path d="M7 11h10M7 15h7"/>
                    </svg>
                    Blog Categories
                </a>
                @endif

                @if($adminUser->hasPermission('blog.view'))
                <a class="nav-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"
                   href="{{ route('admin.blogs.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 3h14v18H5z"/>
                        <path d="M8 7h8M8 11h8M8 15h5"/>
                    </svg>
                    Blog Posts
                </a>
                @endif
            </nav>
        </aside>

        <div class="sidebar-overlay" data-sidebar-overlay></div>

        <div class="admin-main">
            <header class="admin-header">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none" type="button" data-sidebar-toggle aria-label="Toggle sidebar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="d-flex align-items-center gap-2">
                @if($adminUser->hasPermission('tenants.switch') && $adminTenants->isNotEmpty())
                <form class="d-none d-md-flex align-items-center gap-2" method="POST" action="{{ route('admin.tenants.switch') }}">
                    @csrf
                    <label class="small text-secondary mb-0" for="admin_tenant_id">Tenant</label>
                    <select class="form-select form-select-sm" id="admin_tenant_id" name="tenant_id" onchange="this.form.submit()" style="min-width: 180px;">
                        @foreach($adminTenants as $tenantOption)
                            <option value="{{ $tenantOption->id }}" @selected($adminCurrentTenant?->id === $tenantOption->id)>
                                {{ $tenantOption->name }}{{ $tenantOption->is_demo ? ' (Demo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                @if($adminUser->hasPermission('notifications.view'))
                <div class="dropdown" data-notification-dropdown
                     data-index-url="{{ route('admin.api.notifications.index') }}"
                     data-count-url="{{ route('admin.api.notifications.unread-count') }}"
                     @if($adminUser->hasPermission('notifications.mark_read')) data-mark-all-url="{{ route('admin.api.notifications.mark-all-read') }}" @endif>
                    <button class="btn btn-light position-relative"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $adminNotificationUnreadCount ? '' : 'd-none' }}"
                              data-notification-count>{{ $adminNotificationUnreadCount }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 notification-dropdown-menu p-0">
                        <div class="d-flex justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">
                            <div class="fw-semibold">Notifications</div>
                            @if($adminUser->hasPermission('notifications.mark_read'))
                                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}" data-notifications-mark-all>
                                    @csrf
                                    <button class="btn btn-sm btn-link text-decoration-none p-0" type="submit">Mark all read</button>
                                </form>
                            @endif
                        </div>
                        <div data-notification-list>
                            @forelse($adminHeaderNotifications as $notification)
                                <a class="dropdown-item notification-dropdown-item {{ $notification->is_read ? '' : 'unread' }}"
                                   href="{{ $notification->targetUrl() }}"
                                   data-notification-link
                                   data-notification-id="{{ $notification->id }}"
                                   @if($adminUser->hasPermission('notifications.mark_read')) data-mark-url="{{ route('admin.api.notifications.mark-read', $notification) }}" @endif>
                                    <div class="d-flex justify-content-between gap-2">
                                        <span class="fw-semibold text-truncate">{{ $notification->title }}</span>
                                        <span class="badge {{ $notification->typeClass() }}">{{ \App\Models\AdminNotification::label($notification->type) }}</span>
                                    </div>
                                    <div class="small text-secondary text-truncate">{{ $notification->message ?: \App\Models\AdminNotification::label($notification->module) }}</div>
                                    <div class="small text-secondary">{{ $notification->created_at?->diffForHumans() }}</div>
                                </a>
                            @empty
                                <div class="px-3 py-4 text-center text-secondary small" data-notification-empty>No unread notifications.</div>
                            @endforelse
                        </div>
                        <div class="border-top p-2">
                            <a class="btn btn-sm btn-outline-primary w-100" href="{{ route('admin.notifications.index') }}">View All</a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
                </div>
            </header>

            <main class="admin-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
