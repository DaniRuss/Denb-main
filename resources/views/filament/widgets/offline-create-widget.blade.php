<x-filament-widgets::widget>
    {{--
        OfflineCreateWidget — Intercepts form submission when offline.

        Architecture:
        - `navigator.onLine = false` → show offline banner
        - Click on any Filament "Create" button is intercepted (capture phase)
        - Widget dispatches Livewire event → CREATE PAGE responds with form data
        - Data is saved to IndexedDB outbox → user redirected to /admin/outbox

        recordType is detected from URL path (no PHP prop passing needed).
    --}}

    {{-- This element ALWAYS mounts. The inner banner only shows when offline. --}}
    <div x-data="offlineCreateWidget()" x-init="init()">

        {{-- ── Offline Banner ── --}}
        <div
            x-show="!isOnline"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-end="opacity-0"
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
                        <svg x-show="isSaving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
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

    <script>
    function offlineCreateWidget() {
        return {
            isOnline: navigator.onLine,
            isSaving: false,

            /** Pending resolve function set while waiting for form data */
            _dataResolve: null,

            /** Detect record type from the current URL — no PHP needed */
            get recordType() {
                const p = window.location.pathname;
                if (p.includes('volunteer-tips')) return 'volunteer_tip';
                return 'awareness_engagement';
            },

            init() {
                window.addEventListener('online',  () => { this.isOnline = true;  });
                window.addEventListener('offline', () => { this.isOnline = false; });

                /**
                 * Intercept ALL form submissions and Clicks in CAPTURE phase.
                 * This acts before Livewire catches them.
                 */
                const interceptor = (e) => {
                    if (this.isOnline) return;

                    // If it's a native form submit event
                    if (e.type === 'submit') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        this.saveOffline();
                        return;
                    }

                    // If it's a click on a button
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
                document.addEventListener('submit', interceptor, true); // Catch Enter key submits
            },

            /**
             * Find the main form component and extract its state entirely from the
             * client-side JS proxy. We cannot use Livewire.dispatch() here because
             * events trigger server roundtrips, which fail when wifi is physically off!
             */
            async collectFormData() {
                if (!window.Livewire) return null;

                try {
                    // Try to find the Filament form's root Livewire component
                    const form = document.querySelector('form[wire\\:submit]');
                    const root = form ? form.closest('[wire\\:id]') : null;

                    if (root) {
                        const comp = window.Livewire.find(root.getAttribute('wire:id'));
                        // In Livewire v3, .get('data') reads from the client-side proxy immediately
                        if (comp) {
                            const data = comp.get('data');
                            if (data) return data;

                            // Fallback to snapshot object if proxy getter fails
                            if (comp.snapshot && comp.snapshot.data && comp.snapshot.data.data) {
                                return comp.snapshot.data.data;
                            }
                        }
                    }

                    // Strict Fallback: brute-force search all components for the one holding 'data'
                    const allComps = window.Livewire.all();
                    for (const c of allComps) {
                        try {
                            const state = c.get('data');
                            if (state && typeof state === 'object' && Object.keys(state).length > 0) {
                                return state; // Return the first one that actually has populated data
                            }
                        } catch (e) { /* ignore elements without a data property */ }
                    }
                } catch (err) {
                    console.error('[Denb] Error extracting client-side form data:', err);
                }

                return null;
            },


            /** Poll until window.DenbDB is ready (loaded via async import). */
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
                    // 1. Wait for IndexedDB module
                    const dbReady = await this.waitForDB();
                    if (!dbReady) {
                        this.toast('Offline storage not ready. Please refresh.', 'error');
                        return;
                    }

                    // 2. Collect form data from the CREATE PAGE via Livewire event
                    this.toast('Reading form data…', 'info');
                    const wireData = await this.collectFormData();

                    if (!wireData || typeof wireData !== 'object' || Object.keys(wireData).length === 0) {
                        this.toast('Could not read form data. Try filling required fields first.', 'error');
                        return;
                    }

                    // 3. Field validation
                    if (this.recordType === 'awareness_engagement') {
                        const required = ['campaign_id', 'engagement_type', 'sub_city_id', 'woreda_id', 'session_datetime'];
                        for (const f of required) {
                            if (!wireData[f]) {
                                this.toast(`Required: ${f.replace(/_/g, ' ')}`, 'error');
                                return;
                            }
                        }
                        // For house_to_house, check if the repeater has at least one citizen name
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

                    // 4. Save to IndexedDB outbox
                    await window.DenbDB.saveEngagement({
                        ...wireData,
                        record_type:        this.recordType,
                        created_at_mobile:  new Date().toISOString(),
                    });

                    this.toast('✓ Saved to Offline Outbox!', 'success');
                    window.dispatchEvent(new CustomEvent('denb:outbox_updated'));

                    // 5. Redirect to Outbox after letting the toast display
                    setTimeout(() => { window.location.href = '/admin/outbox'; }, 1200);

                } catch (err) {
                    console.error('[Denb] saveOffline error:', err);
                    this.toast('Save failed: ' + (err.message || 'Unknown error'), 'error');
                } finally {
                    this.isSaving = false;
                }
            },

            toast(msg, type = 'info') {
                if (window.DenbUI?.showToast) {
                    window.DenbUI.showToast(msg, type);
                } else {
                    // Fallback if DenbUI hasn't loaded yet
                    console.log(`[Denb Toast][${type}] ${msg}`);
                }
            },
        };
    }
    </script>
</x-filament-widgets::widget>
