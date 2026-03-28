<div>
    <x-filament-panels::page>
        <div x-data="denbOutbox()" x-init="init()" class="space-y-6 relative">
            {{-- ── Session Lock Overlay ── --}}
            <div x-show="idleLock.isLocked || idleLock.setupMode" 
                 class="absolute inset-0 z-[100] flex items-center justify-center backdrop-blur-md bg-white/60 dark:bg-black/60 px-4 min-h-[600px] rounded-xl"
                 x-transition.opacity.duration.300ms style="display: none;">
                <div class="w-full max-w-md shadow-2xl">
                    <div class="rounded-2xl border border-gray-200 bg-white ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10 p-8 text-center shadow-xl">
                        
                        <template x-if="idleLock.setupMode">
                            <div class="space-y-6">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/50">
                                    <x-filament::icon icon="heroicon-o-shield-check" class="h-8 w-8 text-primary-600 dark:text-primary-400" />
                                </div>
                                <h3 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Setup Offline PIN</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Create a secure 4-digit PIN to protect citizen data.</p>
                                <input type="password" x-model="idleLock.inputPin" placeholder="****" class="block w-full rounded-lg border-0 py-3 text-center tracking-[0.5em] font-bold text-2xl ring-1 ring-inset ring-gray-300 dark:bg-white/5 dark:text-white dark:ring-white/20" maxlength="4">
                                <div class="flex flex-col gap-3">
                                    <x-filament::button size="lg" color="primary" @click="saveSetPin()" class="w-full">Set Secure PIN</x-filament::button>
                                    <button @click="skipPin()" class="text-sm text-gray-500">Skip for now</button>
                                </div>
                            </div>
                        </template>

                        <template x-if="idleLock.isLocked && !idleLock.setupMode">
                            <div class="space-y-6">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-danger-50 dark:bg-danger-900/50">
                                    <x-filament::icon icon="heroicon-m-lock-closed" class="h-10 w-10 text-danger-600 dark:text-danger-400" />
                                </div>
                                <h3 class="text-2xl font-bold text-gray-950 dark:text-white">Session Locked</h3>
                                <input type="password" x-model="idleLock.inputPin" placeholder="****" class="block w-full rounded-lg border-0 py-4 text-center tracking-[1em] font-bold text-3xl ring-1 ring-inset ring-gray-300 dark:bg-white/5 dark:text-white dark:ring-white/20" maxlength="4">
                                <div class="pt-2">
                                    <x-filament::button size="lg" color="primary" @click="unlockPin()" class="w-full">Unlock Session</x-filament::button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ── Header Bar ── --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-2">
                <div class="flex flex-col gap-2">
                    <h1 class="text-3xl font-extrabold text-gray-950 dark:text-white">Offline Outbox</h1>
                    <div class="flex items-center gap-3">
                        <template x-if="isOnline">
                            <x-filament::badge color="success" icon="heroicon-m-wifi">Online</x-filament::badge>
                        </template>
                        <template x-if="!isOnline">
                            <x-filament::badge color="danger" icon="heroicon-m-signal-slash">Offline</x-filament::badge>
                        </template>
                        <span class="text-sm font-semibold text-gray-500"><span x-text="drafts.length"></span> record(s)</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-filament::button color="gray" icon="heroicon-m-arrow-path" @click="refreshMasterData()" x-bind:disabled="!isOnline || refreshing">Refresh Data</x-filament::button>
                    <x-filament::button color="primary" icon="heroicon-m-arrow-up-tray" @click="syncAll()" x-bind:disabled="!isOnline || syncing || progressState.pending === 0">Sync All</x-filament::button>
                </div>
            </div>

            {{-- ── Tabs ── --}}
            <x-filament::tabs label="Outbox Tabs">
                <x-filament::tabs.item @click="activeTab = 'pending'" x-bind:active="activeTab === 'pending'">
                    <span class="font-semibold">Pending</span> <span x-text="progressState.pending" class="ml-1 text-xs px-1.5 bg-gray-100 dark:bg-gray-800 rounded"></span>
                </x-filament::tabs.item>
                <x-filament::tabs.item @click="activeTab = 'failed'" x-bind:active="activeTab === 'failed'">
                    <span class="font-semibold text-danger-600">Fix Needed</span> <span x-text="progressState.failed" class="ml-1 text-xs px-1.5 bg-danger-50 dark:bg-danger-900 rounded"></span>
                </x-filament::tabs.item>
                <x-filament::tabs.item @click="activeTab = 'synced'" x-bind:active="activeTab === 'synced'">
                    <span class="font-semibold text-success-600">Synced</span>
                </x-filament::tabs.item>
            </x-filament::tabs>

            {{-- ── Draft List ── --}}
            <div class="mt-4 space-y-4">

                {{-- Loading State --}}
                <div x-show="loading" class="flex items-center justify-center py-12 text-gray-400">
                    <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="ml-3 text-sm font-medium">Loading outbox…</span>
                </div>

                {{-- Empty State --}}
                <div x-show="!loading && filteredDrafts.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 py-16 px-4 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <x-filament::icon icon="heroicon-o-inbox" class="h-7 w-7 text-gray-400" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-700 dark:text-gray-300">Outbox is empty</h3>
                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">
                        Records saved offline will appear here. Go offline on your device and use <strong>Save to Device</strong> when creating an Engagement or Tip.
                    </p>
                </div>

                {{-- Record List --}}
                <template x-for="draft in filteredDrafts" :key="draft.local_uuid">
                    <x-filament::section compact>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <template x-if="draft._outbox_status === 'synced'"><x-filament::badge color="success">Synced</x-filament::badge></template>
                                    <template x-if="draft._outbox_status === 'pending'"><x-filament::badge color="warning">Pending</x-filament::badge></template>
                                    <template x-if="draft._outbox_status === 'error_needs_fix'"><x-filament::badge color="danger">Fix Needed</x-filament::badge></template>
                                    <span class="text-sm font-bold uppercase tracking-tighter" :class="draft.record_type === 'volunteer_tip' ? 'text-primary-600' : 'text-amber-600'" x-text="formatType(draft)"></span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-950 dark:text-white" x-text="draft.citizen_name || draft.suspect_name || '—'"></h4>
                                <p class="text-xs text-gray-400" x-text="formatDate(draft.created_at_mobile)"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-filament::button color="gray" size="sm" @click="openEditModal(draft)" x-show="draft.record_type !== 'volunteer_tip'">Edit</x-filament::button>
                                <x-filament::button color="danger" size="sm" @click="confirmDelete(draft)">Delete</x-filament::button>
                            </div>
                        </div>
                    </x-filament::section>
                </template>
            </div>

            <x-filament::modal id="edit-draft-modal" width="lg">
                <x-slot name="heading">Edit Draft</x-slot>
                <div class="p-4" x-show="editModal.draft">
                    <template x-if="editModal.draft">
                        <div>
                            <label class="block text-sm font-medium">Citizen Name</label>
                            <input type="text" x-model="editModal.draft.citizen_name" class="block w-full rounded-lg border-gray-300 mt-1">
                        </div>
                    </template>
                </div>
                <x-slot name="footer">
                    <x-filament::button color="primary" @click="saveDraftEdits()">Save</x-filament::button>
                </x-slot>
            </x-filament::modal>

            <x-filament::modal id="delete-draft-modal" width="sm">
                <x-slot name="heading">Delete record?</x-slot>
                <x-slot name="footer">
                    <x-filament::button color="danger" @click="deleteDraft()">Delete</x-filament::button>
                </x-slot>
            </x-filament::modal>
        </div>
    </x-filament-panels::page>
    <script>
    function denbOutbox() {
        return {
            drafts: [], loading: true, syncing: false, refreshing: false, isOnline: navigator.onLine,
            syncProgress: { synced: 0, failed: 0, total: 0 }, deleteModal: { draft: null }, editModal: { draft: null },
            activeTab: 'pending', idleLock: { isLocked: false, setupMode: false, pin: '', inputPin: '', error: false },

            get filteredDrafts() {
                return this.drafts.filter(d =>
                    this.activeTab === 'pending'  ? ['pending','syncing'].includes(d._outbox_status) :
                    this.activeTab === 'failed'   ? ['failed','error_needs_fix'].includes(d._outbox_status) :
                    d._outbox_status === 'synced'
                );
            },

            get progressState() {
                return {
                    pending: this.drafts.filter(d => ['pending','syncing'].includes(d._outbox_status)).length,
                    failed:  this.drafts.filter(d => ['failed','error_needs_fix'].includes(d._outbox_status)).length,
                    synced:  this.drafts.filter(d => d._outbox_status === 'synced').length,
                };
            },

            async init() {
                window.addEventListener('online',  () => this.isOnline = true);
                window.addEventListener('offline', () => this.isOnline = false);
                this.initSessionLock();
                await this.loadDrafts();

                // Re-load whenever a record is saved offline from another part of the app
                window.addEventListener('denb:outbox_updated', () => this.loadDrafts());
            },

            initSessionLock() {
                this.idleLock.pin = localStorage.getItem('denb_offline_pin');
                if (this.idleLock.pin === null) this.idleLock.setupMode = true;
            },

            /**
             * Wait for window.DenbDB (loaded async) before reading drafts.
             * Polls every 200ms, gives up after 5s.
             */
            async loadDrafts() {
                this.loading = true;
                try {
                    const step = 200, maxMs = 5000;
                    let elapsed = 0;
                    while (!window.DenbDB && elapsed < maxMs) {
                        await new Promise(r => setTimeout(r, step));
                        elapsed += step;
                    }
                    if (window.DenbDB) {
                        this.drafts = await window.DenbDB.getAllDrafts();
                    } else {
                        console.warn('[Denb Outbox] DenbDB never loaded — check app.js bundle.');
                    }
                } finally {
                    this.loading = false;
                }
            },

            async syncAll() {
                if (!window.DenbSync || this.syncing) return;
                this.syncing = true;
                try {
                    await window.DenbSync.syncOutbox({ onProgress: p => this.syncProgress = p });
                    await this.loadDrafts();
                } finally {
                    this.syncing = false;
                }
            },

            openEditModal(draft) {
                this.editModal.draft = JSON.parse(JSON.stringify(draft));
                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'edit-draft-modal' } }));
            },

            async saveDraftEdits() {
                if (!this.editModal.draft) return;
                const updated = this.editModal.draft;
                updated._outbox_status = 'pending';
                await window.DenbDB.saveEngagement(updated);
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'edit-draft-modal' } }));
                await this.loadDrafts();
            },

            confirmDelete(draft) {
                this.deleteModal.draft = draft;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'delete-draft-modal' } }));
            },

            async deleteDraft() {
                if (!this.deleteModal.draft) return;
                await window.DenbDB.deleteDraft(this.deleteModal.draft.local_uuid);
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'delete-draft-modal' } }));
                await this.loadDrafts();
            },

            formatType(d) {
                if (d.record_type === 'volunteer_tip') return 'Volunteer Tip | ጥቆማ';
                return 'Engagement Log | ምዝገባ';
            },

            formatDate(iso) { return iso ? new Date(iso).toLocaleString('en-ET') : '—'; },

            saveSetPin() {
                if (this.idleLock.inputPin.length >= 4) {
                    localStorage.setItem('denb_offline_pin', this.idleLock.inputPin);
                    this.idleLock.pin = this.idleLock.inputPin;
                    this.idleLock.setupMode = false;
                }
            },
            skipPin()   { localStorage.setItem('denb_offline_pin', ''); this.idleLock.setupMode = false; },
            unlockPin() {
                if (this.idleLock.inputPin === this.idleLock.pin) {
                    this.idleLock.isLocked = false;
                    this.idleLock.inputPin = '';
                    localStorage.setItem('denb_last_activity', Date.now());
                }
            },
            async refreshMasterData() {
                if (!window.DenbSync) return;
                this.refreshing = true;
                try { await window.DenbSync.refreshMasterData(); } finally { this.refreshing = false; }
            },
        };
    }
    </script>
</div>
