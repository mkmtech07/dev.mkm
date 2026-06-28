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

const mediaPickerModal = document.querySelector('[data-media-picker-modal]');

if (mediaPickerModal) {
    const modal = new bootstrap.Modal(mediaPickerModal);
    const resultsElement = mediaPickerModal.querySelector('[data-media-picker-results]');
    const loadingElement = mediaPickerModal.querySelector('[data-media-picker-loading]');
    const emptyElement = mediaPickerModal.querySelector('[data-media-picker-empty]');
    const errorElement = mediaPickerModal.querySelector('[data-media-picker-error]');
    const searchInput = mediaPickerModal.querySelector('[data-media-picker-search]');
    const typeSelect = mediaPickerModal.querySelector('[data-media-picker-type]');
    const previousButton = mediaPickerModal.querySelector('[data-media-picker-prev]');
    const nextButton = mediaPickerModal.querySelector('[data-media-picker-next]');
    const refreshButton = mediaPickerModal.querySelector('[data-media-picker-refresh]');
    let activeField = null;
    let currentPage = 1;
    let lastPage = 1;
    let requestController = null;

    const mediaEscape = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const setLoading = (isLoading) => {
        loadingElement?.classList.toggle('d-none', !isLoading);
        resultsElement?.classList.toggle('d-none', isLoading);
    };

    const setError = (message = '') => {
        if (! errorElement) return;
        errorElement.textContent = message;
        errorElement.classList.toggle('d-none', !message);
    };

    const updatePager = () => {
        if (previousButton) previousButton.disabled = currentPage <= 1;
        if (nextButton) nextButton.disabled = currentPage >= lastPage;
    };

    const fileTypeLabel = (fileType) => String(fileType || 'file').replace(/\b\w/g, (letter) => letter.toUpperCase());

    const renderMediaCard = (item) => {
        const preview = item.is_image
            ? `<img class="media-picker-thumb" src="${mediaEscape(item.url)}" alt="${mediaEscape(item.alt_text || item.title || item.original_name || 'Media image')}" loading="lazy">`
            : `<div class="media-picker-document"><span>${mediaEscape((item.original_name || 'FILE').split('.').pop()?.toUpperCase() || 'FILE')}</span></div>`;

        return `
            <div class="col-sm-6 col-lg-4">
                <article class="card h-100 media-picker-card">
                    <div class="media-picker-preview bg-light border-bottom">${preview}</div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <h3 class="h6 text-truncate mb-0" title="${mediaEscape(item.title || item.original_name)}">${mediaEscape(item.title || item.original_name || 'Untitled media')}</h3>
                            <span class="badge text-bg-light">${mediaEscape(fileTypeLabel(item.file_type))}</span>
                        </div>
                        <div class="small text-secondary text-truncate" title="${mediaEscape(item.original_name)}">${mediaEscape(item.original_name || '')}</div>
                        <div class="small text-secondary">${mediaEscape(item.formatted_size || '')}</div>
                    </div>
                    <div class="card-footer bg-white">
                        <button
                            class="btn btn-sm btn-primary w-100"
                            type="button"
                            data-media-picker-select
                            data-media='${mediaEscape(JSON.stringify(item))}'
                        >Select</button>
                    </div>
                </article>
            </div>
        `;
    };

    const renderResults = (items = []) => {
        if (! resultsElement) return;
        resultsElement.innerHTML = items.map(renderMediaCard).join('');
        emptyElement?.classList.toggle('d-none', items.length > 0);
    };

    const queryParams = () => {
        const params = new URLSearchParams({
            page: String(currentPage),
            per_page: '12',
            accept_type: activeField?.dataset.acceptType || 'any',
        });
        const search = searchInput?.value.trim();
        const fileType = typeSelect?.value;
        if (search) params.set('search', search);
        if (fileType) params.set('file_type', fileType);

        return params;
    };

    const loadMedia = async () => {
        if (! activeField || ! mediaPickerModal.dataset.indexUrl) return;
        requestController?.abort();
        requestController = new AbortController();
        setLoading(true);
        setError('');

        try {
            const response = await fetch(`${mediaPickerModal.dataset.indexUrl}?${queryParams()}`, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });

            if (! response.ok) {
                throw new Error('Unable to load media files.');
            }

            const payload = await response.json();
            currentPage = Number(payload.meta?.current_page || 1);
            lastPage = Number(payload.meta?.last_page || 1);
            renderResults(Array.isArray(payload.data) ? payload.data : []);
            updatePager();
        } catch (error) {
            if (error.name !== 'AbortError') {
                renderResults([]);
                setError(error.message || 'Unable to load media files.');
            }
        } finally {
            setLoading(false);
        }
    };

    const selectedElements = (field) => ({
        id: field.querySelector('[data-media-picker-id]'),
        action: field.querySelector('[data-media-picker-action]'),
        selected: field.querySelector('[data-media-picker-selected]'),
        image: field.querySelector('[data-media-picker-preview-image]'),
        title: field.querySelector('[data-media-picker-preview-title]'),
        meta: field.querySelector('[data-media-picker-preview-meta]'),
    });

    const setSelectedMedia = (field, item) => {
        const elements = selectedElements(field);
        if (! elements.id || ! elements.action) return;

        elements.id.value = item.id;
        elements.action.value = 'select';
        elements.selected?.classList.remove('d-none');

        if (elements.image) {
            elements.image.src = item.is_image ? item.url : '';
            elements.image.classList.toggle('d-none', !item.is_image);
        }
        if (elements.title) elements.title.textContent = item.title || item.original_name || 'Selected media';
        if (elements.meta) elements.meta.textContent = `${fileTypeLabel(item.file_type)} - ${item.formatted_size || ''}`;
    };

    const clearSelectedMedia = (field) => {
        const elements = selectedElements(field);
        if (! elements.id || ! elements.action) return;

        elements.id.value = '';
        elements.action.value = 'clear';
        elements.selected?.classList.remove('d-none');
        elements.image?.classList.add('d-none');
        if (elements.title) elements.title.textContent = 'Current media will be removed.';
        if (elements.meta) elements.meta.textContent = 'Save the form to apply this change.';
    };

    const resetSelectedMediaForUpload = (field) => {
        const elements = selectedElements(field);
        if (! elements.id || ! elements.action) return;

        elements.id.value = '';
        elements.action.value = '';
        elements.selected?.classList.add('d-none');
    };

    document.addEventListener('click', (event) => {
        const openButton = event.target.closest('[data-media-picker-open]');
        if (openButton) {
            activeField = openButton.closest('[data-media-picker-field]');
            currentPage = 1;
            if (searchInput) searchInput.value = '';
            if (typeSelect) {
                const acceptType = activeField?.dataset.acceptType || 'any';
                typeSelect.value = acceptType === 'any' ? '' : acceptType;
                typeSelect.disabled = acceptType !== 'any';
            }
            modal.show();
            loadMedia();
            return;
        }

        const clearButton = event.target.closest('[data-media-picker-clear]');
        if (clearButton) {
            clearSelectedMedia(clearButton.closest('[data-media-picker-field]'));
            return;
        }

        const selectButton = event.target.closest('[data-media-picker-select]');
        if (selectButton && activeField) {
            const item = JSON.parse(selectButton.dataset.media || '{}');
            setSelectedMedia(activeField, item);
            modal.hide();
        }
    });

    document.addEventListener('change', (event) => {
        const input = event.target;
        if (! input.matches('input[type="file"][name]') || ! input.files?.length) return;
        document
            .querySelectorAll(`[data-media-picker-field][data-field-name="${CSS.escape(input.name)}"]`)
            .forEach(resetSelectedMediaForUpload);
    });

    refreshButton?.addEventListener('click', () => {
        currentPage = 1;
        loadMedia();
    });
    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            currentPage = 1;
            loadMedia();
        }
    });
    typeSelect?.addEventListener('change', () => {
        currentPage = 1;
        loadMedia();
    });
    previousButton?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage -= 1;
            loadMedia();
        }
    });
    nextButton?.addEventListener('click', () => {
        if (currentPage < lastPage) {
            currentPage += 1;
            loadMedia();
        }
    });

    document.querySelectorAll('[data-media-picker-field]').forEach((field) => {
        const id = field.querySelector('[data-media-picker-id]')?.value;
        const action = field.querySelector('[data-media-picker-action]')?.value;
        if (! id || action !== 'select') return;

        fetch(`/admin/api/media-picker/${encodeURIComponent(id)}`, { headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : null))
            .then((payload) => {
                if (payload?.data) setSelectedMedia(field, payload.data);
            })
            .catch(() => {});
    });
}
