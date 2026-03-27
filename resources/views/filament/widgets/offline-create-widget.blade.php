<x-filament-widgets::widget>
    <div
        x-data="offlineCreateWidget()"
        x-init="initWidget"
        class="hidden"
        :class="{ '!block': !isOnline }"
    >
        <div class="rounded-xl border border-dashed border-red-700 bg-red-950 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-900 text-red-300 ring-4 ring-red-900/30">
                    <svg class="h-4 w-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243-2.829a4.978 4.978 0 011.415-2.828m-1.415 2.828l-2.829 2.829m2.829-2.829a4.978 4.978 0 01-1.415-2.828m0 0l2.829-2.829M5.636 5.636a9 9 0 0112.728 0m-12.728 0L3 3" />
                    </svg>
                </span>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-200">Offline Mode Active</h3>
                    <p class="mt-1 text-xs text-red-400">
                        You have no network connection. Your input will be saved locally as a Draft when you press the button below.
                    </p>
                </div>
                <div class="shrink-0">
                    <button
                        type="button"
                        @click="saveOffline"
                        :disabled="isSaving"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2 font-semibold text-white shadow hover:bg-red-600 disabled:opacity-50"
                    >
                        <svg x-show="!isSaving" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <svg x-show="isSaving" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="isSaving ? 'Saving…' : 'Save to Device'"></span>
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

                initWidget() {
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);

                    // When offline, we want to hide the original Filament 'Create' bottom sticky action bar
                    this.$watch('isOnline', (online) => {
                        this.toggleOriginalActions(online);
                    });
                    
                    setTimeout(() => this.toggleOriginalActions(this.isOnline), 300);
                },

                toggleOriginalActions(online) {
                    const actionBars = document.querySelectorAll('.filament-page-actions, .fi-form-actions');
                    actionBars.forEach(bar => {
                        if (!online) {
                            bar.style.display = 'none';
                        } else {
                            bar.style.display = '';
                        }
                    });
                },

                async saveOffline() {
                    const wireData = await this.$wire.get('data');
                    if (!wireData) return;

                    // 1. Core Field Validation
                    if (!wireData.campaign_id || !wireData.engagement_type || !wireData.sub_city_id || !wireData.woreda_id || !wireData.session_datetime || !wireData.violation_type) {
                        window.DenbUI && window.DenbUI.showToast('Please fill all required core fields (Campaign, Type, Location, Violation, Date)', 'error');
                        return;
                    }

                    // 2. Type-Specific Validation
                    if (wireData.engagement_type === 'house_to_house' && !wireData.citizen_name) {
                        window.DenbUI && window.DenbUI.showToast('House-to-House requires Citizen Name!', 'error');
                        return;
                    }
                    if (wireData.engagement_type === 'coffee_ceremony' && !wireData.headcount) {
                        window.DenbUI && window.DenbUI.showToast('Coffee Ceremony requires Headcount!', 'error');
                        return;
                    }

                    // 3. Signature Validation
                    if (!wireData.officer_signature) {
                        window.DenbUI && window.DenbUI.showToast('Please draw your signature before saving.', 'error');
                        return;
                    }

                    this.isSaving = true;
                    try {
                        if (window.DenbDB) {
                            const localUuid = await window.DenbDB.saveEngagement({
                                ...wireData,
                                created_at_mobile: new Date().toISOString()
                            });
                            window.DenbUI && window.DenbUI.showToast('Saved to Offline Outbox ✓', 'success');
                            window.dispatchEvent(new CustomEvent('denb:outbox_updated'));
                            
                            // Let the toast display, then redirect
                            setTimeout(() => {
                                window.location.href = '/admin/outbox';
                            }, 1000);
                        } else {
                            alert("Offline DB is not loaded.");
                        }
                    } catch (e) {
                        console.error("Offline save Error:", e);
                        window.DenbUI && window.DenbUI.showToast('Error saving. Local storage might be full.', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
</x-filament-widgets::widget>
