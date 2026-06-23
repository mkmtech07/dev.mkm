<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { siteSettings } from '../siteSettings';

const currentYear = new Date().getFullYear();
const fallbackData = {
    settings: {
        footer_logo: siteSettings.logoUrl,
        footer_description: siteSettings.tagline || `${siteSettings.siteName} helps businesses work smarter with dependable digital solutions.`,
        phone: null,
        email: null,
        whatsapp: null,
        address: null,
        copyright_text: `© ${currentYear} ${siteSettings.siteName}. All rights reserved.`,
        newsletter_status: false,
    },
    sections: [
        {
            title: 'About',
            type: 'about',
            content: `Practical technology and thoughtful service from ${siteSettings.siteName}.`,
            links: [],
        },
        {
            title: 'Quick Links',
            type: 'links',
            content: null,
            links: [
                { title: 'Home', url: '/', target: '_self' },
                { title: 'About', url: '/about', target: '_self' },
                { title: 'Services', url: '/services', target: '_self' },
                { title: 'Gallery', url: '/gallery', target: '_self' },
                { title: 'Blog', url: '/blog', target: '_self' },
                { title: 'Contact', url: '/contact', target: '_self' },
            ],
        },
        {
            title: 'Contact',
            type: 'contact',
            content: 'Have a question? Send us a message through the contact page.',
            links: [{ title: 'Contact us', url: '/contact', target: '_self' }],
        },
        {
            title: 'Follow Us',
            type: 'social',
            content: null,
            links: [],
        },
    ],
    social_links: [],
};

const fallbackSocialLinks = [
    { platform: 'Facebook', url: '#', icon: null, target: '_self' },
    { platform: 'Instagram', url: '#', icon: null, target: '_self' },
    { platform: 'LinkedIn', url: '#', icon: null, target: '_self' },
];

const footerData = ref(fallbackData);
const requestController = new AbortController();
const newsletterEmail = ref('');
const newsletterLoading = ref(false);
const newsletterSuccess = ref('');
const newsletterError = ref('');
const newsletterValidation = ref({});
let newsletterController;

const settings = computed(() => {
    const footerSettings = footerData.value.settings || {};

    return {
        ...fallbackData.settings,
        ...footerSettings,
        footer_logo: footerSettings.footer_logo || siteSettings.whiteLogoUrl || siteSettings.logoUrl,
        footer_description: footerSettings.footer_description || siteSettings.tagline,
        phone: footerSettings.phone || siteSettings.phone,
        email: footerSettings.email || siteSettings.email,
        whatsapp: footerSettings.whatsapp || siteSettings.whatsapp,
        address: footerSettings.address || siteSettings.address,
    };
});
const sections = computed(() => (footerData.value.sections || []).filter((section) => (
    section.type !== 'newsletter' || settings.value.newsletter_status
)));
const socialLinks = computed(() => {
    if (footerData.value.social_links?.length) return footerData.value.social_links;

    const globalLinks = [
        { platform: 'Facebook', url: siteSettings.facebookUrl, icon: 'bi bi-facebook', target: '_blank' },
        { platform: 'Instagram', url: siteSettings.instagramUrl, icon: 'bi bi-instagram', target: '_blank' },
        { platform: 'LinkedIn', url: siteSettings.linkedinUrl, icon: 'bi bi-linkedin', target: '_blank' },
        { platform: 'YouTube', url: siteSettings.youtubeUrl, icon: 'bi bi-youtube', target: '_blank' },
        { platform: 'Twitter / X', url: siteSettings.twitterUrl, icon: 'bi bi-twitter-x', target: '_blank' },
    ].filter((link) => link.url);

    return globalLinks.length ? globalLinks : fallbackSocialLinks;
});
const hasSection = (type) => sections.value.some((section) => section.type === type);
const isInternal = (link) => (
    link?.target !== '_blank'
    && typeof link?.url === 'string'
    && link.url.startsWith('/')
    && ! link.url.startsWith('//')
);
const whatsappUrl = computed(() => {
    const number = String(settings.value.whatsapp || '').replace(/[^0-9]/g, '');
    return number ? `https://wa.me/${number}` : null;
});
const defaultSocialIcons = {
    facebook: 'bi bi-facebook',
    instagram: 'bi bi-instagram',
    linkedin: 'bi bi-linkedin',
    youtube: 'bi bi-youtube',
    'twitter / x': 'bi bi-twitter-x',
    twitter: 'bi bi-twitter-x',
    x: 'bi bi-twitter-x',
};
const socialIcon = (social) => (
    social.icon || defaultSocialIcons[String(social.platform || '').toLowerCase()] || ''
);

const subscribeNewsletter = async () => {
    if (newsletterLoading.value) return;

    newsletterController?.abort();
    newsletterController = new AbortController();
    newsletterLoading.value = true;
    newsletterSuccess.value = '';
    newsletterError.value = '';
    newsletterValidation.value = {};

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/api/newsletter/subscribe', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ email: newsletterEmail.value, source: 'footer', website: '' }),
            signal: newsletterController.signal,
        });
        const payload = await response.json().catch(() => ({}));

        if (response.status === 422) {
            newsletterValidation.value = payload.errors || {};
            throw new Error(newsletterValidation.value.email?.[0] || 'Please enter a valid email address.');
        }
        if (! response.ok) throw new Error(payload.message || 'Subscription is temporarily unavailable.');

        newsletterSuccess.value = payload.message || 'Thank you for subscribing.';
        newsletterEmail.value = '';
    } catch (error) {
        if (error.name !== 'AbortError') newsletterError.value = error.message || 'Subscription is temporarily unavailable.';
    } finally {
        newsletterLoading.value = false;
    }
};

onMounted(async () => {
    try {
        const response = await fetch('/api/footer', {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });
        if (! response.ok) return;

        const payload = await response.json();
        if (payload.settings) {
            footerData.value = {
                settings: payload.settings,
                sections: Array.isArray(payload.sections) ? payload.sections : [],
                social_links: Array.isArray(payload.social_links) ? payload.social_links : [],
            };
        }
    } catch (error) {
        if (error.name !== 'AbortError') footerData.value = fallbackData;
    }
});

onBeforeUnmount(() => {
    requestController.abort();
    newsletterController?.abort();
});
</script>

<template>
    <footer class="dynamic-footer bg-dark text-white">
        <div class="container py-5">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img v-if="settings.footer_logo" class="footer-logo mb-3" :src="settings.footer_logo" :alt="`${siteSettings.siteName} footer logo`">
                    <h2 v-else class="h4 fw-bold">{{ siteSettings.siteName }}</h2>
                    <p v-if="settings.footer_description" class="text-white-50 footer-copy">{{ settings.footer_description }}</p>

                    <ul v-if="!hasSection('contact') && (settings.phone || settings.email || settings.address)" class="list-unstyled small text-white-50 mb-3">
                        <li v-if="settings.phone" class="mb-2"><a class="footer-link" :href="`tel:${settings.phone}`">{{ settings.phone }}</a></li>
                        <li v-if="settings.email" class="mb-2"><a class="footer-link" :href="`mailto:${settings.email}`">{{ settings.email }}</a></li>
                        <li v-if="settings.address" class="footer-copy">{{ settings.address }}</li>
                    </ul>

                    <div v-if="!hasSection('social') && socialLinks.length" class="footer-socials">
                        <a v-for="social in socialLinks" :key="`${social.platform}-${social.url}`" class="social-link" :href="social.url" :target="social.target" :rel="social.target === '_blank' ? 'noopener noreferrer' : null" :aria-label="social.platform">
                            <i v-if="socialIcon(social)" :class="socialIcon(social)" aria-hidden="true"></i><span v-else>{{ social.platform.slice(0, 1) }}</span>
                        </a>
                    </div>
                </div>

                <div v-for="section in sections" :key="`${section.type}-${section.title}`" class="footer-section">
                    <h2 class="h6 text-uppercase fw-bold mb-3">{{ section.title }}</h2>

                    <p v-if="['about', 'links', 'social', 'custom'].includes(section.type) && section.content" class="footer-copy text-white-50">{{ section.content }}</p>

                    <template v-if="section.type === 'contact'">
                        <p v-if="section.content" class="footer-copy text-white-50">{{ section.content }}</p>
                        <ul class="list-unstyled small mb-0">
                            <li v-if="settings.phone" class="mb-2"><a class="footer-link" :href="`tel:${settings.phone}`">{{ settings.phone }}</a></li>
                            <li v-if="settings.email" class="mb-2"><a class="footer-link" :href="`mailto:${settings.email}`">{{ settings.email }}</a></li>
                            <li v-if="whatsappUrl" class="mb-2"><a class="footer-link" :href="whatsappUrl" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
                            <li v-if="settings.address" class="footer-copy text-white-50">{{ settings.address }}</li>
                        </ul>
                    </template>

                    <div v-if="section.type === 'social'" class="footer-socials">
                        <a v-for="social in socialLinks" :key="`${social.platform}-${social.url}`" class="social-link" :href="social.url" :target="social.target" :rel="social.target === '_blank' ? 'noopener noreferrer' : null" :aria-label="social.platform">
                            <i v-if="socialIcon(social)" :class="socialIcon(social)" aria-hidden="true"></i><span v-else>{{ social.platform.slice(0, 1) }}</span>
                        </a>
                        <span v-if="!socialLinks.length" class="small text-white-50">No social profiles available.</span>
                    </div>

                    <template v-if="section.type === 'newsletter'">
                        <p v-if="section.content" class="footer-copy text-white-50">{{ section.content }}</p>
                        <form class="newsletter-form" @submit.prevent="subscribeNewsletter">
                            <label class="visually-hidden" :for="`newsletter-email-${section.title}`">Email address</label>
                            <div class="input-group">
                                <input
                                    :id="`newsletter-email-${section.title}`"
                                    v-model.trim="newsletterEmail"
                                    class="form-control"
                                    :class="{ 'is-invalid': newsletterValidation.email }"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="Your email address"
                                    required
                                    :disabled="newsletterLoading"
                                >
                                <button class="btn btn-primary" type="submit" :disabled="newsletterLoading">
                                    <span v-if="newsletterLoading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                    <span v-else>Subscribe</span>
                                </button>
                            </div>
                            <div v-if="newsletterSuccess" class="small text-success-emphasis bg-success-subtle rounded px-2 py-1 mt-2" role="status">{{ newsletterSuccess }}</div>
                            <div v-if="newsletterError" class="small text-danger-emphasis bg-danger-subtle rounded px-2 py-1 mt-2" role="alert">{{ newsletterError }}</div>
                        </form>
                    </template>

                    <ul v-if="section.links?.length" class="list-unstyled mb-0">
                        <li v-for="link in section.links" :key="`${link.title}-${link.url}`" class="mb-2">
                            <RouterLink v-if="isInternal(link)" class="footer-link" :to="link.url"><i v-if="link.icon" :class="[link.icon, 'me-2']" aria-hidden="true"></i>{{ link.title }}</RouterLink>
                            <a v-else class="footer-link" :href="link.url" :target="link.target || '_self'" :rel="link.target === '_blank' ? 'noopener noreferrer' : null"><i v-if="link.icon" :class="[link.icon, 'me-2']" aria-hidden="true"></i>{{ link.title }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom border-top border-secondary border-opacity-25 py-3">
            <div class="container text-center text-md-start small text-white-50">
                {{ settings.copyright_text || `© ${currentYear} ${siteSettings.siteName}. All rights reserved.` }}
            </div>
        </div>
    </footer>
</template>

<style scoped>
.footer-grid {
    display: grid;
    grid-template-columns: minmax(17rem, 2fr) repeat(3, minmax(10rem, 1fr));
    gap: 2.5rem 2rem;
    align-items: start;
}

.footer-logo {
    max-height: 64px;
    max-width: 190px;
    object-fit: contain;
}

.footer-copy {
    overflow-wrap: break-word;
    white-space: pre-line;
}

.footer-link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
}

.footer-link:hover,
.footer-link.router-link-active {
    color: #fff;
}

.footer-socials {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.6rem;
    align-items: center;
}

.newsletter-form .form-control {
    min-width: 0;
}

.newsletter-form .btn {
    white-space: nowrap;
}

.social-link {
    display: inline-grid;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    place-items: center;
    padding: 0;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 50%;
    transition: background-color 160ms ease, border-color 160ms ease, transform 160ms ease;
}

.social-link i {
    font-size: 1.05rem;
    line-height: 1;
}

.social-link:hover {
    color: #fff;
    background: var(--bs-primary);
    border-color: var(--bs-primary);
    transform: translateY(-2px);
}

@media (max-width: 1199.98px) {
    .footer-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .footer-brand {
        grid-column: 1 / -1;
        max-width: 40rem;
    }
}

@media (max-width: 767.98px) {
    .footer-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 2rem 1.5rem;
    }
}

@media (max-width: 575.98px) {
    .footer-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .footer-brand {
        grid-column: auto;
    }
}
</style>
