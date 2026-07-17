/**
 * Bento Bridge - A lightweight SPA-like navigation system for NHMP 130 CRM
 * 
 * Intercepts sidebar links and form submissions, fetches content via AJAX, 
 * and swaps the main content area for a seamless, no-reload experience.
 */
document.addEventListener('DOMContentLoaded', () => {
    const mainArea = document.querySelector('main');
    const headerArea = document.querySelector('header');
    const sidebar = document.querySelector('aside');

    // Add loading progress bar
    const createProgressBar = () => {
        let bar = document.getElementById('bento-nav-progress');
        if (bar) return bar;

        bar = document.createElement('div');
        bar.id = 'bento-nav-progress';
        bar.className = 'fixed top-0 left-0 h-1 bg-blue-600 z-[10000] transition-all duration-300 opacity-0';
        bar.style.width = '0%';
        document.body.appendChild(bar);
        return bar;
    };

    const progressBar = createProgressBar();

    const startProgress = () => {
        window.dispatchEvent(new CustomEvent('bento:page-loading'));
        progressBar.style.width = '0%';
        progressBar.style.opacity = '1';
        setTimeout(() => progressBar.style.width = '30%', 10);
        setTimeout(() => progressBar.style.width = '60%', 200);
    };

    const endProgress = () => {
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => progressBar.style.width = '0%', 300);
        }, 200);
    };

    /**
     * core page loading logic
     */
    const loadPage = async (url, options = { pushState: true, method: 'GET', body: null }) => {
        startProgress();

        try {
            const fetchOptions = {
                method: options.method || 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-PJAX': 'true',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            };

            if (options.body) {
                fetchOptions.body = options.body;
            }

            const response = await fetch(url, fetchOptions);

            // Handle security redirects/403s
            if (response.status === 403) {
                const text = await response.text();
                if (text === 'MFA_REQUIRED') {
                    window.location.href = '/2fa';
                    return;
                }
            }

            if (!response.ok) {
                // If it's a validation error (422), we still want to show the form errors
                if (response.status !== 422) {
                    throw new Error(`Network response ${response.status}`);
                }
            }

            // If we were redirected (e.g. after a POST), use the final URL for history.pushState
            const finalUrl = response.url;
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Swap Main Content
            const newMain = doc.querySelector('main');
            if (newMain && mainArea) {
                mainArea.innerHTML = newMain.innerHTML;
                mainArea.scrollTop = 0;
            }

            // Swap Header
            const newHeader = doc.querySelector('header');
            if (newHeader && headerArea) {
                headerArea.innerHTML = newHeader.innerHTML;
            }

            // Sync Sidebar Active State natively from fetched document
            if (sidebar) {
                const currentNavItems = sidebar.querySelectorAll('.nav-item');
                const newNavItems = doc.querySelectorAll('aside .nav-item');

                currentNavItems.forEach((item, index) => {
                    const newItem = newNavItems[index];
                    if (newItem) {
                        // Strip any rogue tailwind classes that might have been added by OLD cached scripts
                        item.classList.remove('bg-blue-600', 'text-white', 'shadow-[0_8px_20px_rgba(37,99,235,0.3)]', 'text-slate-500', 'hover:text-blue-600');
                        const icon = item.querySelector('i');
                        if (icon) {
                            icon.classList.remove('text-blue-500/60', 'text-white/80');
                        }

                        // Mirror server truth
                        if (newItem.classList.contains('active')) {
                            item.classList.add('active');
                        } else {
                            item.classList.remove('active');
                        }
                    }
                });
            }

            // Update Page Title
            document.title = doc.title;

            if (options.pushState) {
                window.history.pushState({ url: finalUrl }, '', finalUrl);
            }

            // Re-execute ONLY inline scripts from the fetched document.
            // This picks up @stack('scripts') blocks that define component factories
            // (e.g. shiftManager, zoneManager, intakeComponent IIFE) which render
            // OUTSIDE <main> and would otherwise be missed.
            //
            // We intentionally SKIP all external src= scripts — CDN libs (Alpine,
            // Collapse plugin, Chart.js) are already loaded and must NOT be re-run
            // because doing so would reset Alpine's plugin registry, causing
            // x-collapse "not installed" warnings on navigated pages.
            const allPageScripts = doc.querySelectorAll('body script, head script');

            allPageScripts.forEach(oldScript => {
                try {
                    // Skip ALL external scripts — they are either already loaded or
                    // should not be reloaded during SPA navigation.
                    if (oldScript.getAttribute('src')) return;

                    // Only run non-empty inline scripts
                    const code = oldScript.innerHTML.trim();
                    if (!code) return;

                    const newScript = document.createElement('script');
                    // Preserve any type attribute (e.g. type="module")
                    const type = oldScript.getAttribute('type');
                    if (type) newScript.setAttribute('type', type);

                    newScript.appendChild(document.createTextNode(code));
                    // Append to body so global functions land on window
                    document.body.appendChild(newScript);
                    // Remove immediately — inline scripts execute synchronously on append
                    document.body.removeChild(newScript);
                } catch (e) {
                    console.error('Bento Bridge: Script Re-init Failed', e, oldScript);
                }
            });

            // Re-init Alpine.js (v3) — runs after all inline scripts have executed
            // so component factories (shiftManager, zoneManager, etc.) are on window.
            // Collapse plugin is already registered globally; initTree picks it up.
            if (window.Alpine) {
                setTimeout(() => {
                    window.Alpine.initTree(mainArea);
                }, 0);
            }

            // Handle Autofocus
            const focusEl = mainArea.querySelector('[autofocus]');
            if (focusEl) {
                setTimeout(() => focusEl.focus(), 100);
            }

            window.dispatchEvent(new CustomEvent('bento:page-loaded', { detail: { url: finalUrl } }));

        } catch (error) {
            console.error('Bento Bridge Error Details:', error);
            // Fallback to reload if AJAX fails catastrophically
            if (options.pushState && options.method === 'GET') {
                window.location.href = url;
            }
        } finally {
            endProgress();
        }
    };
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link || e.which > 1 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const isInternal = link.hostname === window.location.hostname;
        const isLogout = link.innerText.toLowerCase().includes('log out') || link.href.includes('logout');
        const isNoPjax = link.hasAttribute('data-no-pjax') || link.getAttribute('target') === '_blank';

        // Force full page reload for guest layout pages (login, etc.)
        if (isInternal && !isLogout && isGuestPage(link.href)) {
            e.preventDefault();
            window.location.href = link.href;
            return;
        }

        if (isInternal && !isLogout && !isNoPjax) {
            e.preventDefault();
            loadPage(link.href);
        }
    });

    // Intercept form submissions
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.hasAttribute('data-no-pjax')) return;

        // Only intercept internal forms
        const actionUrl = new URL(form.action, window.location.origin);
        if (actionUrl.hostname !== window.location.hostname) return;

        // Force full page reload for guest layout pages (login, etc.)
        // IMPORTANT: Do NOT preventDefault — let the native form submission
        // go through (POST with credentials). SPA navigation would lose POST data.
        if (isGuestPage(form.action)) {
            return; // let browser handle form naturally
        }

        e.preventDefault();

        const formData = new FormData(form);
        const method = (form.getAttribute('method') || 'GET').toUpperCase();

        loadPage(form.action, {
            pushState: true,
            method: method,
            body: method === 'GET' ? null : formData
        });
    });

    // Detect guest-only pages that must do a full page load to swap layouts
    const isGuestPage = (url) => {
        const guestPaths = ['/login', '/forgot-password', '/reset-password', '/2fa'];
        const path = new URL(url, window.location.origin).pathname;
        return guestPaths.some(gp => path === gp || path.startsWith(gp + '/'));
    };

    // Handle back/forward
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.url) {
            if (isGuestPage(e.state.url)) {
                window.location.href = e.state.url;
                return;
            }
            loadPage(e.state.url, { pushState: false });
        } else {
            loadPage(window.location.href, { pushState: false });
        }
    });
});
