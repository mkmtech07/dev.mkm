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
