<x-filament-widgets::widget>
    <div x-data="offlineCreateWidget()" x-init="init()">

        {{-- ── Offline Banner (only visible when offline) ── --}}
        <div
            x-show="!isOnline"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            style="display: none;"
            class="mb-2 relative overflow-hidden rounded-2xl border border-danger-300 bg-danger-50 dark:border-danger-700/50 dark:bg-danger-950/30 p-4 shadow-xl"
        >
            {{-- Glow effect --}}
            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-danger-400/20 blur-2xl"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {{-- Status Info --}}
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-danger-500/20 ring-1 ring-danger-500/30">
                        <x-filament::icon icon="heroicon-m-signal-slash" class="h-5 w-5 animate-pulse text-danger-600 dark:text-danger-400" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-danger-800 dark:text-danger-300">Offline Mode Active</h3>
                        <p class="mt-0.5 text-xs text-danger-700/70 dark:text-danger-400/70">
                            No network connection. Fill the form then press <strong>Save to Device</strong> below — your record will be queued in the Outbox.
                        </p>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="shrink-0 pl-12 sm:pl-0">
                    <button
                        @click.prevent="saveOffline()"
                        :disabled="isSaving"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-danger-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg ring-1 ring-danger-700 transition hover:bg-danger-700 active:scale-95 disabled:opacity-60"
                    >
                        <svg x-show="!isSaving" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <svg x-show="isSaving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="isSaving ? 'Saving to Outbox…' : 'Save to Device'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function offlineCreateWidget() {
        return {
            isOnline: navigator.onLine,
            isSaving: false,

            /**
             * Detect record type from the current URL path.
             * No PHP data passing needed — URL is always accurate.
             */
            get recordType() {
                return window.location.pathname.includes('volunteer-tips') ? 'volunteer_tip' : 'awareness_engagement';
            },

            init() {
                // Listen to native browser online/offline events
                window.addEventListener('online',  () => { this.isOnline = true;  });
                window.addEventListener('offline', () => { this.isOnline = false; });

                /**
                 * KEY FIX: Intercept ALL button clicks from the Filament form in
                 * CAPTURE phase (runs before Livewire's own listener).
                 * When we're offline, any click on a "create" or "submit" type
                 * Livewire action is hijacked → we run saveOffline() instead.
                 */
                document.addEventListener('click', (e) => {
                    if (this.isOnline) return; // online → let Livewire handle normally

                    const btn = e.target.closest('button, [type="submit"]');
                    if (!btn) return;

                    const wireAction = btn.getAttribute('wire:click') || '';
                    const isFilamentCreate = wireAction.includes('create') || wireAction.includes('save') || btn.type === 'submit';

                    if (isFilamentCreate) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        this.saveOffline();
                    }
                }, true); // <<< capture phase = before Livewire
            },

            /**
             * Wait for window.DenbDB to be ready (it loads async via import()).
             * Polls up to 4 seconds before giving up.
             */
            async waitForDB(maxMs = 4000) {
                const step = 150;
                let elapsed = 0;
                while (!window.DenbDB && elapsed < maxMs) {
                    await new Promise(r => setTimeout(r, step));
                    elapsed += step;
                }
                return !!window.DenbDB;
            },

            async saveOffline() {
                this.isSaving = true;
                try {
                    // 1. Ensure the DB module is loaded
                    const dbReady = await this.waitForDB();
                    if (!dbReady) {
                        window.DenbUI?.showToast('Offline storage not ready. Please refresh.', 'error');
                        return;
                    }

                    // 2. Pull the current form state from Livewire
                    let wireData = null;
                    try {
                        wireData = await this.$wire.get('data');
                    } catch(err) {
                        window.DenbUI?.showToast('Could not read form data. Try again.', 'error');
                        return;
                    }
                    if (!wireData) return;

                    // 3. Field validation per record type
                    if (this.recordType === 'awareness_engagement') {
                        const required = [
                            'campaign_id', 'engagement_type', 'sub_city_id',
                            'woreda_id', 'session_datetime', 'violation_type', 'officer_signature'
                        ];
                        for (const f of required) {
                            if (!wireData[f]) {
                                window.DenbUI?.showToast(`Required field missing: ${f.replace(/_/g, ' ')}`, 'error');
                                return;
                            }
                        }
                        if (wireData.engagement_type === 'house_to_house' && !wireData.citizen_name) {
                            window.DenbUI?.showToast('Citizen name is required for House-to-House.', 'error');
                            return;
                        }
                    } else if (this.recordType === 'volunteer_tip') {
                        const required = [
                            'violation_type', 'violation_location', 'sub_city_id',
                            'woreda_id', 'violation_date', 'reported_date', 'volunteer_signature_path'
                        ];
                        for (const f of required) {
                            if (!wireData[f]) {
                                window.DenbUI?.showToast(`Required field missing: ${f.replace(/_/g, ' ')}`, 'error');
                                return;
                            }
                        }
                    }

                    // 4. Save to IndexedDB outbox
                    await window.DenbDB.saveEngagement({
                        ...wireData,
                        record_type: this.recordType,
                        created_at_mobile: new Date().toISOString(),
                    });

                    window.DenbUI?.showToast('✓ Record queued in Offline Outbox', 'success');
                    window.dispatchEvent(new CustomEvent('denb:outbox_updated'));

                    // 5. Redirect to outbox after short delay so toast is visible
                    setTimeout(() => { window.location.href = '/admin/outbox'; }, 1100);

                } catch (err) {
                    console.error('[Denb] saveOffline error:', err);
                    window.DenbUI?.showToast('Failed to save offline. Check console.', 'error');
                } finally {
                    this.isSaving = false;
                }
            },
        };
    }
    </script>
</x-filament-widgets::widget>
