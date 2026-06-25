import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

const closeSidebar = () => document.body.classList.remove('sidebar-open');

sidebarToggle?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
});

sidebarOverlay?.addEventListener('click', closeSidebar);

window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) {
        closeSidebar();
    }
});

const notificationDropdown = document.querySelector('[data-notification-dropdown]');

if (notificationDropdown) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const countElement = notificationDropdown.querySelector('[data-notification-count]');
    const listElement = notificationDropdown.querySelector('[data-notification-list]');

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const typeClass = (type) => ({
        info: 'text-bg-info',
        success: 'text-bg-success',
        warning: 'text-bg-warning',
        danger: 'text-bg-danger',
        system: 'text-bg-dark',
    }[type] || 'text-bg-secondary');

    const label = (value) => String(value || 'system').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

    const updateCount = (count) => {
        if (! countElement) return;
        const value = Number(count || 0);
        countElement.textContent = value > 99 ? '99+' : String(value);
        countElement.classList.toggle('d-none', value <= 0);
    };

    const renderNotifications = (items = []) => {
        if (! listElement) return;
        if (! items.length) {
            listElement.innerHTML = '<div class="px-3 py-4 text-center text-secondary small" data-notification-empty>No unread notifications.</div>';
            return;
        }

        listElement.innerHTML = items.map((item) => `
            <a class="dropdown-item notification-dropdown-item unread"
               href="${escapeHtml(item.url || item.show_url || '#')}"
               data-notification-link
               data-notification-id="${escapeHtml(item.id)}"
               data-mark-url="/admin/api/notifications/${escapeHtml(item.id)}/mark-as-read">
                <div class="d-flex justify-content-between gap-2">
                    <span class="fw-semibold text-truncate">${escapeHtml(item.title)}</span>
                    <span class="badge ${typeClass(item.type)}">${escapeHtml(label(item.type))}</span>
                </div>
                <div class="small text-secondary text-truncate">${escapeHtml(item.message || label(item.module))}</div>
                <div class="small text-secondary">${escapeHtml(item.time_ago || '')}</div>
            </a>
        `).join('');
    };

    const fetchNotifications = async () => {
        try {
            const response = await fetch(notificationDropdown.dataset.indexUrl, {
                headers: { Accept: 'application/json' },
            });
            if (! response.ok) return;
            const payload = await response.json();
            updateCount(payload.unread_count);
            renderNotifications(payload.data || []);
        } catch {
            // Header polling is an enhancement; the admin panel must keep working.
        }
    };

    notificationDropdown.addEventListener('click', (event) => {
        const link = event.target.closest('[data-notification-link]');
        if (! link?.dataset.markUrl) return;

        navigator.sendBeacon?.(link.dataset.markUrl, new Blob([new URLSearchParams({ _token: csrf })], {
            type: 'application/x-www-form-urlencoded',
        }));
    });

    notificationDropdown.querySelector('[data-notifications-mark-all]')?.addEventListener('submit', async (event) => {
        const url = notificationDropdown.dataset.markAllUrl;
        if (! url) return;
        event.preventDefault();

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            });
            if (! response.ok) return;
            updateCount(0);
            renderNotifications([]);
        } catch {
            event.target.submit();
        }
    });

    if (notificationDropdown.dataset.indexUrl) {
        window.setInterval(fetchNotifications, 60000);
    }
}
