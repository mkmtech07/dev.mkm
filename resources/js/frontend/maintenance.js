import { reactive } from 'vue';

const fallback = {
    enabled: false,
    mode: 'frontend_only',
    title: '',
    message: '',
    image: '',
    button_text: '',
    button_url: '',
    start_at: '',
    end_at: '',
    retry_after_minutes: 60,
    meta_robots: 'noindex',
    custom_css: '',
};

const injected = window.__MAINTENANCE_STATUS__ || null;

export const maintenanceStatus = reactive({
    ...fallback,
    ...(injected || {}),
});

const ensureMeta = (selector, attributes) => {
    let element = document.querySelector(selector);
    if (! element) {
        element = document.createElement('meta');
        Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
        document.head.appendChild(element);
    }

    return element;
};

const ensureCanonical = () => {
    let element = document.querySelector('link[rel="canonical"]');
    if (! element) {
        element = document.createElement('link');
        element.rel = 'canonical';
        document.head.appendChild(element);
    }

    return element;
};

export const applyMaintenanceMeta = () => {
    document.getElementById('maintenance-custom-css')?.remove();
    window.__MAINTENANCE_ACTIVE__ = Boolean(maintenanceStatus.enabled);

    if (! maintenanceStatus.enabled) return;

    document.title = maintenanceStatus.title || 'Website Under Maintenance';
    ensureMeta('meta[name="robots"]', { name: 'robots' }).content = maintenanceStatus.meta_robots === 'index'
        ? 'index, follow'
        : 'noindex, nofollow';
    ensureMeta('meta[name="description"]', { name: 'description' }).content = maintenanceStatus.message
        || 'We are currently improving our website. Please check back soon.';
    ensureCanonical().href = window.location.href;

    if (maintenanceStatus.custom_css) {
        const style = document.createElement('style');
        style.id = 'maintenance-custom-css';
        style.textContent = maintenanceStatus.custom_css;
        document.head.appendChild(style);
    }
};

export const loadMaintenanceStatus = async () => {
    const path = encodeURIComponent(window.location.pathname || '/');

    try {
        const response = await fetch(`/api/maintenance-status?path=${path}`, {
            headers: { Accept: 'application/json' },
        });
        if (! response.ok) throw new Error('Maintenance status unavailable');
        const payload = await response.json();
        Object.assign(maintenanceStatus, { ...fallback, ...payload });
    } catch {
        if (! injected) {
            Object.assign(maintenanceStatus, fallback);
        }
    }

    applyMaintenanceMeta();

    return maintenanceStatus;
};
