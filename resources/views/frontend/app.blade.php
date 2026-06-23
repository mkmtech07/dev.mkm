<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $websiteSettings->meta_description ?: 'Professional website powered by Laravel and Vue' }}">
        <meta name="keywords" content="{{ $websiteSettings->meta_keywords }}">
        <meta property="og:title" content="{{ $websiteSettings->meta_title ?: ($websiteSettings->site_name ?: 'CMS Website') }}">
        <meta property="og:description" content="{{ $websiteSettings->meta_description ?: 'Professional website powered by Laravel and Vue' }}">
        <meta property="og:image" content="{{ $websiteSettings->og_image ? asset($websiteSettings->og_image) : '' }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $websiteSettings->meta_title ?: ($websiteSettings->site_name ?: 'CMS Website') }}">
        <meta name="twitter:description" content="{{ $websiteSettings->meta_description ?: 'Professional website powered by Laravel and Vue' }}">
        <meta name="twitter:image" content="{{ $websiteSettings->og_image ? asset($websiteSettings->og_image) : '' }}">
        <meta name="robots" content="{{ $seoSettings->default_robots_index ? 'index' : 'noindex' }}, {{ $seoSettings->default_robots_follow ? 'follow' : 'nofollow' }}">
        <link rel="canonical" href="{{ url()->current() }}">

        <title>{{ $websiteSettings->meta_title ?: ($websiteSettings->site_name ?: 'CMS Website') }}</title>

        @if ($websiteSettings->favicon)
            <link rel="icon" href="{{ asset($websiteSettings->favicon) }}">
        @endif

        <script>
            window.__SITE_SETTINGS__ = {{ Illuminate\Support\Js::from([
                'siteName' => $websiteSettings->site_name ?: 'CMS Website',
                'tagline' => $websiteSettings->site_tagline ?: 'Professional Website CMS',
                'logoUrl' => $websiteSettings->logo ? asset($websiteSettings->logo) : null,
                'whiteLogoUrl' => $websiteSettings->white_logo ? asset($websiteSettings->white_logo) : null,
                'faviconUrl' => $websiteSettings->favicon ? asset($websiteSettings->favicon) : null,
                'ogImageUrl' => $websiteSettings->og_image ? asset($websiteSettings->og_image) : null,
                'primaryColor' => $websiteSettings->primary_color ?: '#0d6efd',
                'secondaryColor' => $websiteSettings->secondary_color ?: '#6c757d',
                'phone' => $websiteSettings->phone,
                'email' => $websiteSettings->email,
                'whatsapp' => $websiteSettings->whatsapp,
                'address' => $websiteSettings->address,
                'googleMapEmbed' => $websiteSettings->google_map_embed,
                'facebookUrl' => $websiteSettings->facebook_url,
                'instagramUrl' => $websiteSettings->instagram_url,
                'linkedinUrl' => $websiteSettings->linkedin_url,
                'youtubeUrl' => $websiteSettings->youtube_url,
                'twitterUrl' => $websiteSettings->twitter_url,
                'metaTitle' => $websiteSettings->meta_title ?: ($websiteSettings->site_name ?: 'CMS Website'),
                'metaDescription' => $websiteSettings->meta_description ?: 'Professional website powered by Laravel and Vue',
                'metaKeywords' => $websiteSettings->meta_keywords,
                'customCss' => $websiteSettings->custom_css,
                'customJs' => $websiteSettings->custom_js,
            ]) }};
            window.__SEO_INTEGRATIONS__ = {{ Illuminate\Support\Js::from([
                'active' => (bool) ($seoSettings->status ?? false),
                'googleAnalytics' => $seoSettings->google_analytics_id,
                'googleTagManager' => $seoSettings->google_tag_manager_id,
                'searchConsole' => $seoSettings->google_search_console_code,
                'facebookPixel' => $seoSettings->facebook_pixel_id,
            ]) }};
        </script>

        @php
            $sanitizeSeoMarkup = static function (?string $markup, string $allowed): string {
                $clean = preg_replace('/<\?(?:php|=).*?\?>|@(?:php|endphp|include|extends|section|yield)\b.*$/ims', '', $markup ?? '') ?? '';
                $clean = strip_tags($clean, $allowed);
                $clean = preg_replace('/\s+on[a-z]+\s*=\s*(["\']).*?\1/is', '', $clean) ?? '';
                return preg_replace('/javascript\s*:/i', '', $clean) ?? '';
            };
        @endphp
        @if ($seoSettings->status && $seoSettings->custom_head_code)
            {!! $sanitizeSeoMarkup($seoSettings->custom_head_code, '<meta><link><style><noscript>') !!}
        @endif

        @vite(['resources/js/frontend/app.js'])
    </head>
    <body>
        <div id="frontend-app"></div>
        @if ($seoSettings->status && $seoSettings->custom_body_code)
            {!! $sanitizeSeoMarkup($seoSettings->custom_body_code, '<noscript><div><span><img><p>') !!}
        @endif
    </body>
</html>
