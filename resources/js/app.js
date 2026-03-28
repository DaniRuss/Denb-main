import './bootstrap';

// ── Denb Offline Layer ────────────────────────────────────────────────────────
// Boot the PWA service worker, IndexedDB store, and connection UI on every page.
// Import is conditional so it doesn't break the public-facing portal pages.
if (document.querySelector('[data-filament-panel]') || window.location.pathname.startsWith('/admin')) {
    Promise.all([
        import('./offline/offline-db.js'),
        import('./offline/offline-sync.js'),
        import('./offline/offline-ui.js'),
    ]).then(([db, sync, ui]) => {
        // Expose modules globally so Alpine.js components can use them
        window.DenbDB   = db;
        window.DenbSync = sync;
        window.DenbUI   = ui;

        // Boot the UI (registers SW, starts connection listener, shows pill)
        ui.boot();
    }).catch((err) => {
        console.warn('[Denb Offline] Failed to load offline modules:', err);
    });
}

// ── Offline Form Widget Data ──────────────────────────────────────────────────
// Defines the logic for the Offline Create Widget banner and form interception.
window.offlineCreateWidget = function() {
    return {
        isOnline: navigator.onLine,
        isSaving: false,

        get recordType() {
            const p = window.location.pathname;
            if (p.includes('volunteer-tips')) return 'volunteer_tip';
            return 'awareness_engagement';
        },

        init() {
            window.addEventListener('online',  () => { this.isOnline = true;  });
            window.addEventListener('offline', () => { this.isOnline = false; });

            setInterval(() => {
                const currentlyOnline = navigator.onLine;
                if (this.isOnline !== currentlyOnline) {
                    this.isOnline = currentlyOnline;
                }

                const buttons = document.querySelectorAll('button[type="submit"], .fi-btn');
                buttons.forEach(btn => {
                    if (btn.closest('[x-data]') && btn.closest('[x-data]').getAttribute('x-data')?.includes('isOnline')) return;

                    const labelSpan = btn.querySelector('.fi-btn-label') || btn;

                    if (!this.isOnline) {
                        if (btn.type !== 'submit' && !btn.getAttribute('wire:click')?.includes('create') && !labelSpan.innerText?.toLowerCase().includes('create')) return;

                        if (!btn.dataset.originalText) {
                            btn.dataset.originalText = labelSpan.innerText;
                            btn.dataset.originalClasses = btn.className;
                        }
                        labelSpan.innerText = 'Save Offline Draft (Outbox)';
                        btn.classList.add('bg-danger-600', 'hover:bg-danger-500', 'ring-danger-500');
                    } else {
                        if (btn.dataset.originalText) {
                            labelSpan.innerText = btn.dataset.originalText;
                            btn.className = btn.dataset.originalClasses;
                            delete btn.dataset.originalText;
                            delete btn.dataset.originalClasses;
                            btn.classList.remove('bg-danger-600', 'hover:bg-danger-500', 'ring-danger-500');
                        }
                    }
                });

            }, 1000);

            const interceptor = (e) => {
                if (this.isOnline) return;

                if (e.type === 'submit') {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    this.saveOffline();
                    return;
                }

                if (e.type === 'click') {
                    const btn = e.target.closest('button');
                    if (!btn) return;

                    const wire = btn.getAttribute('wire:click') || '';
                    if (wire.includes('create') || wire.includes('store') || btn.type === 'submit') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        this.saveOffline();
                    }
                }
            };

            document.addEventListener('click', interceptor, true);
            document.addEventListener('submit', interceptor, true);
        },

        async collectFormData() {
            if (!window.Livewire) return null;

            try {
                const form = document.querySelector('.fi-form') || document.querySelector('form[wire\\\\:submit]');
                const root = form ? form.closest('[wire\\\\:id]') : null;

                if (root) {
                    const comp = window.Livewire.find(root.getAttribute('wire:id'));
                    if (comp) {
                        let data = window.Alpine?.raw ? window.Alpine.raw(comp.data) || comp.data : comp.data;
                        if (!data && typeof comp.get === 'function') data = comp.get('data');
                        
                        if (!data && comp.snapshot && comp.snapshot.data && comp.snapshot.data.data) {
                            data = comp.snapshot.data.data;
                        }
                        
                        if (data) return JSON.parse(JSON.stringify(data));
                    }
                }

                const allComps = window.Livewire.all();
                for (const c of allComps) {
                    try {
                        const state = c.data || (typeof c.get === 'function' ? c.get('data') : null);
                        if (state && typeof state === 'object' && Object.keys(state).length > 0) {
                            return JSON.parse(JSON.stringify(state));
                        }
                    } catch (e) { }
                }
            } catch (err) {
                console.error('[Denb] Error extracting client-side form data:', err);
            }

            return null;
        },

        async waitForDB(maxMs = 5000) {
            const step = 200;
            let elapsed = 0;
            while (!window.DenbDB && elapsed < maxMs) {
                await new Promise(r => setTimeout(r, step));
                elapsed += step;
            }
            return !!window.DenbDB;
        },

        async saveOffline() {
            if (this.isSaving) return;
            this.isSaving = true;

            try {
                const dbReady = await this.waitForDB();
                if (!dbReady) {
                    this.toast('Offline storage not ready. Please refresh.', 'error');
                    return;
                }

                this.toast('Reading form data…', 'info');
                const wireData = await this.collectFormData();

                if (!wireData || typeof wireData !== 'object' || Object.keys(wireData).length === 0) {
                    this.toast('Could not read form data. Try filling required fields first.', 'error');
                    return;
                }

                if (this.recordType === 'awareness_engagement') {
                    const required = ['campaign_id', 'engagement_type', 'sub_city_id', 'woreda_id', 'session_datetime'];
                    for (const f of required) {
                        if (!wireData[f]) {
                            this.toast(`Required: ${f.replace(/_/g, ' ')}`, 'error');
                            return;
                        }
                    }
                    if (wireData.engagement_type === 'house_to_house') {
                        const persons = Object.values(wireData.registered_persons || {});
                        if (persons.length === 0 || !persons[0].citizen_name) {
                            this.toast('Citizen name required for House-to-House.', 'error');
                            return;
                        }
                    }
                } else if (this.recordType === 'volunteer_tip') {
                    const required = ['violation_type', 'violation_location', 'sub_city_id', 'woreda_id', 'violation_date', 'reported_date'];
                    for (const f of required) {
                        if (!wireData[f]) {
                            this.toast(`Required: ${f.replace(/_/g, ' ')}`, 'error');
                            return;
                        }
                    }
                }

                await window.DenbDB.saveEngagement({
                    ...wireData,
                    record_type:        this.recordType,
                    created_at_mobile:  new Date().toISOString(),
                });

                this.toast('✓ Saved to Offline Outbox!', 'success');
                window.dispatchEvent(new CustomEvent('denb:outbox_updated'));

                setTimeout(() => { window.location.href = '/admin/outbox'; }, 1000);

            } catch (err) {
                console.error('[Denb] saveOffline error:', err);
                this.toast('Save failed: ' + (err.message || 'Unknown error'), 'error');
            } finally {
                this.isSaving = false;
            }
        },

        toast(msg, type = 'info') {
            if (window.DenbUI && typeof window.DenbUI.showToast === 'function') {
                window.DenbUI.showToast(msg, type);
            } else if (window.Toastify) {
                window.Toastify({ text: msg, style: { background: type === 'error' ? '#ef4444' : '#10b981' } }).showToast();
            } else {
                console.log(`[Denb Toast][${type}] ${msg}`);
                if (type === 'error') alert(msg);
            }
        },
    };
};
