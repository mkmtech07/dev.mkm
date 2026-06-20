<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
        @stack('styles')
    </head>
    <body>
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/>
                    </svg>
                </span>
                <span>{{ config('app.name', 'Laravel') }}</span>
            </a>

            <nav class="nav flex-column pb-4">
                <div class="admin-nav-label">Main</div>

                <a class="nav-link {{ request()->routeIs('admin.dashboard', 'dashboard') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 12l9-9 9 9"/>
                        <path d="M5 10v11h14V10M9 21v-7h6v7"/>
                    </svg>
                    Dashboard
                </a>

                <div class="admin-nav-label">Website</div>

                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                   href="{{ route('admin.settings.edit') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.6v-.2h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1z"/>
                    </svg>
                    Website Settings
                </a>

                <a class="nav-link {{ request()->routeIs('admin.hero-sliders.*') ? 'active' : '' }}"
                   href="{{ route('admin.hero-sliders.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="8.5" cy="9" r="1.5"/>
                        <path d="m3 16 5-4 4 3 3-2 6 4"/>
                    </svg>
                    Hero Sliders
                </a>

                <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}"
                   href="{{ route('admin.services.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h10"/>
                        <circle cx="18" cy="17" r="2"/>
                    </svg>
                    Services
                </a>

                <a class="nav-link {{ request()->routeIs('admin.about.*') ? 'active' : '' }}"
                   href="{{ route('admin.about.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>
                    About Us
                </a>

                <a class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}"
                   href="{{ route('admin.gallery.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="8.5" cy="9" r="1.5"/>
                        <path d="m3 16 5-4 4 3 3-2 6 4"/>
                    </svg>
                    Gallery
                </a>

                <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}"
                   href="{{ route('admin.testimonials.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16v11H8l-4 4z"/>
                        <path d="M8 9h8M8 12h5"/>
                    </svg>
                    Testimonials
                </a>

                <a class="nav-link {{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"
                   href="{{ route('admin.team-members.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="9" cy="8" r="3"/>
                        <circle cx="17" cy="10" r="2"/>
                        <path d="M3 20a6 6 0 0 1 12 0M14 16a5 5 0 0 1 7 4"/>
                    </svg>
                    Team Members
                </a>

                <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"
                   href="{{ route('admin.pages.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 2h8l4 4v16H6z"/>
                        <path d="M14 2v5h5M9 12h6M9 16h6"/>
                    </svg>
                    Pages
                </a>

                <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}"
                   href="{{ route('admin.faqs.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.1 9a3 3 0 1 1 4.8 2.4c-1.1.8-1.9 1.3-1.9 2.6M12 18h.01"/>
                    </svg>
                    FAQs
                </a>

                <a class="nav-link {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}"
                   href="{{ route('admin.blog-categories.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 5h7l2 2h9v12H3z"/>
                        <path d="M7 11h10M7 15h7"/>
                    </svg>
                    Blog Categories
                </a>

                <a class="nav-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"
                   href="{{ route('admin.blogs.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 3h14v18H5z"/>
                        <path d="M8 7h8M8 11h8M8 15h5"/>
                    </svg>
                    Blog Posts
                </a>
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
            </header>

            <main class="admin-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
