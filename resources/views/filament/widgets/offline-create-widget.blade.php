<x-filament-widgets::widget>
    {{-- This element ALWAYS mounts. The inner banner only shows when offline. --}}
    <div x-data="offlineCreateWidgetData" x-init="initWidget()">

        {{-- ── Offline Banner ── --}}
        <div
            x-show="!isOnline"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="display:none;"
            class="mb-3 relative overflow-hidden rounded-2xl border border-danger-300 bg-danger-50/80 dark:border-danger-700/50 dark:bg-danger-950/40 p-4 shadow-xl backdrop-blur-sm"
        >
            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-danger-400/20 blur-2xl"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-danger-500/20 ring-1 ring-danger-500/30">
                        <x-filament::icon icon="heroicon-m-signal-slash" class="h-5 w-5 animate-pulse text-danger-600 dark:text-danger-400" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-danger-800 dark:text-danger-300">Offline Mode Active</h3>
                        <p class="mt-0.5 text-xs text-danger-700/70 dark:text-danger-400/70">
                            No network. Fill the form then click <strong>Save to Device</strong> — your record will be queued in the Outbox.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 pl-12 sm:pl-0">
                    <button
                        @click.prevent="saveOffline()"
                        :disabled="isSaving"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-danger-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg ring-1 ring-danger-700 transition hover:bg-danger-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg x-show="!isSaving" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <svg x-show="isSaving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="isSaving ? 'Saving…' : 'Save to Device'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Hidden listener: receives form data from the page Livewire component ── --}}
        <div
            x-on:denb-offline-data-ready.window="onDataReady($event.detail)"
            style="display:none;"
        ></div>
    </div>
</x-filament-widgets::widget>

@script
<script>
    Alpine.data('offlineCreateWidgetData', () => ({
        isOnline: navigator.onLine,
        isSaving: false,

        get recordType() {
            const p = window.location.pathname;
            if (p.includes('volunteer-tips')) return 'volunteer_tip';
            return 'awareness_engagement';
        },

        initWidget() {
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
        }
    }));
</script>
@endscript
