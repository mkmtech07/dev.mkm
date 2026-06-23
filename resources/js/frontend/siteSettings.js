import { reactive } from 'vue';

const injected = window.__SITE_SETTINGS__ || {};
const fallback = {
    siteName: 'CMS Website',
    tagline: 'Professional Website CMS',
    logoUrl: null,
    whiteLogoUrl: null,
    faviconUrl: null,
    ogImageUrl: null,
    primaryColor: '#0d6efd',
    secondaryColor: '#6c757d',
    phone: null,
    email: null,
    whatsapp: null,
    address: null,
    googleMapEmbed: null,
    facebookUrl: null,
    instagramUrl: null,
    linkedinUrl: null,
    youtubeUrl: null,
    twitterUrl: null,
    metaTitle: 'CMS Website',
    metaDescription: 'Professional website powered by Laravel and Vue',
    metaKeywords: null,
    customCss: null,
    customJs: null,
};

export const siteSettings = reactive({ ...fallback, ...injected });

const ensureMeta = (selector, attributes) => {
    let element = document.querySelector(selector);
    if (! element) {
        element = document.createElement('meta');
        Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
        document.head.appendChild(element);
    }
    return element;
};

export const applySeo = (seo = {}) => {
    const resolvedTitle = seo.meta_title || seo.title || siteSettings.metaTitle || siteSettings.siteName;
    const resolvedDescription = seo.meta_description || seo.description || siteSettings.metaDescription || '';
    const resolvedKeywords = seo.meta_keywords || seo.keywords || siteSettings.metaKeywords || '';
    const resolvedOgTitle = seo.og_title || resolvedTitle;
    const resolvedOgDescription = seo.og_description || resolvedDescription;
    const resolvedImage = seo.og_image || seo.image || siteSettings.ogImageUrl || '';
    const resolvedTwitterImage = seo.twitter_image || resolvedImage;

    document.title = resolvedTitle;
    ensureMeta('meta[name="description"]', { name: 'description' }).content = resolvedDescription;
    ensureMeta('meta[name="keywords"]', { name: 'keywords' }).content = resolvedKeywords;
    ensureMeta('meta[property="og:title"]', { property: 'og:title' }).content = resolvedOgTitle;
    ensureMeta('meta[property="og:description"]', { property: 'og:description' }).content = resolvedOgDescription;
    ensureMeta('meta[property="og:image"]', { property: 'og:image' }).content = resolvedImage;
    ensureMeta('meta[name="twitter:card"]', { name: 'twitter:card' }).content = resolvedTwitterImage ? 'summary_large_image' : 'summary';
    ensureMeta('meta[name="twitter:title"]', { name: 'twitter:title' }).content = seo.twitter_title || resolvedOgTitle;
    ensureMeta('meta[name="twitter:description"]', { name: 'twitter:description' }).content = seo.twitter_description || resolvedOgDescription;
    ensureMeta('meta[name="twitter:image"]', { name: 'twitter:image' }).content = resolvedTwitterImage;
    const index = seo.robots_index ?? true;
    const follow = seo.robots_follow ?? true;
    ensureMeta('meta[name="robots"]', { name: 'robots' }).content = `${index ? 'index' : 'noindex'}, ${follow ? 'follow' : 'nofollow'}`;

    let canonicalElement = document.querySelector('link[rel="canonical"]');
    if (! canonicalElement) {
        canonicalElement = document.createElement('link');
        canonicalElement.rel = 'canonical';
        document.head.appendChild(canonicalElement);
    }
    canonicalElement.href = seo.canonical_url || seo.canonical || window.location.href;
};

let seoRequest = 0;

export const loadRouteSeo = async (path = window.location.pathname) => {
    const requestId = ++seoRequest;
    try {
        const response = await fetch(`/api/seo?path=${encodeURIComponent(path)}`, { headers: { Accept: 'application/json' } });
        if (! response.ok) throw new Error('SEO metadata unavailable');
        const seo = await response.json();
        if (requestId === seoRequest) applySeo(seo);
    } catch {
        if (requestId === seoRequest) applySeo({ canonical: window.location.href });
    }
};

export const loadSchemaMarkup = async () => {
    document.querySelectorAll('script[data-managed-schema]').forEach((script) => script.remove());
    try {
        const response = await fetch('/api/seo/schema', { headers: { Accept: 'application/json' } });
        if (! response.ok) return;
        const payload = await response.json();
        (Array.isArray(payload.data) ? payload.data : []).forEach((item) => {
            if (! item?.schema || typeof item.schema !== 'object') return;
            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.dataset.managedSchema = 'true';
            script.textContent = JSON.stringify(item.schema).replace(/</g, '\\u003c');
            document.head.appendChild(script);
        });
    } catch {
        // Structured data is an enhancement; page rendering must remain unaffected.
    }
};

export const loadTrackingIntegrations = () => {
    const integrations = window.__SEO_INTEGRATIONS__ || {};
    if (! integrations.active) return;

    const verification = String(integrations.searchConsole || '').match(/content=["']([^"']+)["']/i)?.[1]
        || String(integrations.searchConsole || '').trim();
    if (verification) ensureMeta('meta[name="google-site-verification"]', { name: 'google-site-verification' }).content = verification;

    const ga = String(integrations.googleAnalytics || '').trim();
    if (/^(G|UA)-[A-Z0-9-]+$/i.test(ga) && ! document.getElementById('seo-google-analytics')) {
        const external = document.createElement('script');
        external.id = 'seo-google-analytics'; external.async = true;
        external.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga)}`;
        document.head.appendChild(external);
        const inline = document.createElement('script');
        inline.textContent = `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',${JSON.stringify(ga)});`;
        document.head.appendChild(inline);
    }

    const gtm = String(integrations.googleTagManager || '').trim();
    if (/^GTM-[A-Z0-9]+$/i.test(gtm) && ! document.getElementById('seo-google-tag-manager')) {
        const script = document.createElement('script'); script.id = 'seo-google-tag-manager';
        script.textContent = `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',${JSON.stringify(gtm)});`;
        document.head.appendChild(script);
    }

    const pixel = String(integrations.facebookPixel || '').trim();
    if (/^\d{5,30}$/.test(pixel) && ! document.getElementById('seo-facebook-pixel')) {
        const script = document.createElement('script'); script.id = 'seo-facebook-pixel';
        script.textContent = `!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',${JSON.stringify(pixel)});fbq('track','PageView');`;
        document.head.appendChild(script);
    }
};

const hexToRgb = (hex) => {
    const value = String(hex || '').replace('#', '');
    if (![3, 6, 8].includes(value.length)) return null;
    const expanded = value.length === 3
        ? value.split('').map((character) => character.repeat(2)).join('')
        : value.slice(0, 6);
    return [0, 2, 4].map((index) => Number.parseInt(expanded.slice(index, index + 2), 16)).join(', ');
};

const applyTheme = () => {
    const root = document.documentElement;
    root.style.setProperty('--site-primary-color', siteSettings.primaryColor || fallback.primaryColor);
    root.style.setProperty('--site-secondary-color', siteSettings.secondaryColor || fallback.secondaryColor);
    root.style.setProperty('--bs-primary', siteSettings.primaryColor || fallback.primaryColor);
    root.style.setProperty('--bs-secondary', siteSettings.secondaryColor || fallback.secondaryColor);
    root.style.setProperty('--bs-link-color', siteSettings.primaryColor || fallback.primaryColor);
    root.style.setProperty('--bs-secondary-color', siteSettings.secondaryColor || fallback.secondaryColor);

    const primaryRgb = hexToRgb(siteSettings.primaryColor);
    const secondaryRgb = hexToRgb(siteSettings.secondaryColor);
    if (primaryRgb) {
        root.style.setProperty('--bs-primary-rgb', primaryRgb);
        root.style.setProperty('--bs-link-color-rgb', primaryRgb);
        root.style.setProperty('--bs-primary-bg-subtle', `rgba(${primaryRgb}, 0.12)`);
        root.style.setProperty('--bs-primary-text-emphasis', siteSettings.primaryColor || fallback.primaryColor);
    }
    if (secondaryRgb) {
        root.style.setProperty('--bs-secondary-rgb', secondaryRgb);
        root.style.setProperty('--bs-secondary-color-rgb', secondaryRgb);
    }
};

const applyFavicon = () => {
    if (! siteSettings.faviconUrl) return;
    let favicon = document.querySelector('link[rel~="icon"]');
    if (! favicon) {
        favicon = document.createElement('link');
        favicon.rel = 'icon';
        document.head.appendChild(favicon);
    }
    favicon.href = siteSettings.faviconUrl;
};

const applyCustomCode = () => {
    document.getElementById('website-custom-css')?.remove();
    if (siteSettings.customCss) {
        const style = document.createElement('style');
        style.id = 'website-custom-css';
        style.textContent = siteSettings.customCss;
        document.head.appendChild(style);
    }

    document.getElementById('website-custom-js')?.remove();
    if (siteSettings.customJs) {
        const script = document.createElement('script');
        script.id = 'website-custom-js';
        script.textContent = siteSettings.customJs;
        document.body.appendChild(script);
    }
};

export const applyWebsiteSettings = () => {
    applyTheme();
    applyFavicon();
    applySeo();
    applyCustomCode();
};

const normalize = (settings) => ({
    siteName: settings.site_name || fallback.siteName,
    tagline: settings.site_tagline || fallback.tagline,
    logoUrl: settings.logo || null,
    whiteLogoUrl: settings.white_logo || null,
    faviconUrl: settings.favicon || null,
    ogImageUrl: settings.og_image || null,
    primaryColor: settings.primary_color || fallback.primaryColor,
    secondaryColor: settings.secondary_color || fallback.secondaryColor,
    phone: settings.phone || null,
    email: settings.email || null,
    whatsapp: settings.whatsapp || null,
    address: settings.address || null,
    googleMapEmbed: settings.google_map_embed || null,
    facebookUrl: settings.facebook_url || null,
    instagramUrl: settings.instagram_url || null,
    linkedinUrl: settings.linkedin_url || null,
    youtubeUrl: settings.youtube_url || null,
    twitterUrl: settings.twitter_url || null,
    metaTitle: settings.meta_title || settings.site_name || fallback.metaTitle,
    metaDescription: settings.meta_description || fallback.metaDescription,
    metaKeywords: settings.meta_keywords || null,
    customCss: settings.custom_css || null,
    customJs: settings.custom_js || null,
});

export const loadWebsiteSettings = async () => {
    try {
        const response = await fetch('/api/website-settings', { headers: { Accept: 'application/json' } });
        if (response.ok) {
            const settings = await response.json();
            if (settings) Object.assign(siteSettings, normalize(settings));
        }
    } catch {
        // The server-rendered values and built-in fallbacks remain available.
    }

    applyWebsiteSettings();
};
