<x-filament-panels::page>

{{-- ══════════════════════════════════════════════════════════════════════════
     OUTBOX PAGE — Client-side rendered via Alpine.js + IndexedDB
     ══════════════════════════════════════════════════════════════════════════ --}}

<div>
<div
    x-data="denbOutbox()"
    x-init="init()"
    class="space-y-6 relative"
>
    {{-- ── Session Lock Overlay ── --}}
    <div x-show="idleLock.isLocked || idleLock.setupMode" 
         class="absolute inset-0 z-[100] flex items-center justify-center backdrop-blur-md bg-white/60 dark:bg-black/60 px-4 min-h-[600px] rounded-xl"
         x-transition.opacity.duration.300ms style="display: none;">
        <div class="w-full max-w-md shadow-2xl">
            {{-- Using raw styled card to guarantee aesthetics even if inside Alpine --}}
            <div class="rounded-2xl border border-gray-200 bg-white ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10 p-8 text-center shadow-xl">
                
                <template x-if="idleLock.setupMode">
                    <div class="space-y-6">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/50">
                            <x-filament::icon icon="heroicon-o-shield-check" class="h-8 w-8 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Setup Offline PIN</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Create a secure 4-digit PIN to protect citizen data when your device goes offline and falls idle in the field.</p>
                        </div>
                        
                        <div>
                            <input type="password" x-model="idleLock.inputPin" placeholder="Enter 4-digit PIN" class="block w-full rounded-lg border-0 py-3 text-center tracking-[0.5em] font-bold text-2xl text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500" maxlength="4">
                        </div>

                        <div class="flex flex-col gap-3">
                            <x-filament::button size="lg" color="primary" @click="saveSetPin()" class="w-full">
                                Set Secure PIN
                            </x-filament::button>
                            <button @click="skipPin()" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Skip for now
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="idleLock.isLocked && !idleLock.setupMode">
                    <div class="space-y-6">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-danger-50 dark:bg-danger-900/50">
                            <x-filament::icon icon="heroicon-m-lock-closed" class="h-10 w-10 text-danger-600 dark:text-danger-400 outline-none" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Session Locked</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">You have been offline and idle for 2 hours. Enter your PIN to unlock the Outbox vault.</p>
                        </div>

                        <div>
                            <input type="password" x-model="idleLock.inputPin" placeholder="****" class="block w-full rounded-lg border-0 py-4 text-center tracking-[1em] font-bold text-3xl text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500" maxlength="4">
                            <p x-show="idleLock.error" class="text-sm font-bold text-danger-600 dark:text-danger-400 mt-2" style="display: none;">Incorrect PIN. Try again.</p>
                        </div>

                        <div class="pt-2">
                            <x-filament::button size="lg" color="primary" @click="unlockPin()" class="w-full">
                                Unlock Session
                            </x-filament::button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Header Bar ── --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-2">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-950 dark:text-white mt-1">
                Offline Outbox
            </h1>
            <div class="flex items-center gap-3">
                <template x-if="isOnline">
                    <x-filament::badge color="success" icon="heroicon-m-wifi">Online - Connected</x-filament::badge>
                </template>
                <template x-if="!isOnline">
                    <x-filament::badge color="danger" icon="heroicon-m-signal-slash" class="animate-pulse">Offline - Local Vault</x-filament::badge>
                </template>

                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                    <span x-text="drafts.length"></span> record(s) on device
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 mt-4 md:mt-0">
            <x-filament::button 
                color="gray" 
                icon="heroicon-m-arrow-path" 
                @click="refreshMasterData()" 
                x-bind:disabled="!isOnline || refreshing"
                class="shadow-sm">
                <span x-text="refreshing ? 'Refreshing...' : 'Refresh Master Data'"></span>
            </x-filament::button>

            <x-filament::button 
                color="primary" 
                icon="heroicon-m-arrow-up-tray" 
                @click="syncAll()" 
                x-bind:disabled="!isOnline || syncing || progressState.pending === 0"
                class="shadow-sm">
                <span x-text="syncing ? 'Syncing...' : 'Sync All Now'"></span>
            </x-filament::button>
        </div>
    </div>

    {{-- ── Progress Bar (during sync) ── --}}
    <div x-show="syncing" x-transition style="display: none;" class="rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-900/50 dark:bg-primary-950/30">
        <div class="flex justify-between text-sm font-bold text-primary-600 dark:text-primary-400 mb-2">
            <span class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4 animate-spin outline-none" />
                Wait, securely transmitting records...
            </span>
            <span x-text="`${syncProgress.synced} / ${syncProgress.total}`"></span>
        </div>
        <div class="h-2 w-full rounded-full bg-primary-200 dark:bg-primary-900/50 overflow-hidden">
            <div class="h-full rounded-full bg-primary-600 transition-all duration-300 dark:bg-primary-500"
                 :style="`width: ${syncProgress.total > 0 ? (syncProgress.synced / syncProgress.total * 100) : 0}%`"></div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <x-filament::tabs label="Outbox Tabs">
        <x-filament::tabs.item @click="activeTab = 'pending'" x-bind:active="activeTab === 'pending'">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-m-clock" class="h-5 w-5 outline-none" />
                <span class="font-semibold">Pending Sync</span>
                <span x-show="progressState.pending > 0" class="inline-flex items-center rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-700/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20" x-text="progressState.pending"></span>
            </div>
        </x-filament::tabs.item>

        <x-filament::tabs.item @click="activeTab = 'failed'" x-bind:active="activeTab === 'failed'">
            <div class="flex items-center gap-2 text-danger-600 dark:text-danger-400">
                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-5 w-5 outline-none" />
                <span class="font-semibold">Fix Needed</span>
                <span x-show="progressState.failed > 0" class="inline-flex items-center rounded-full bg-danger-50 px-2 py-0.5 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20" x-text="progressState.failed"></span>
            </div>
        </x-filament::tabs.item>

        <x-filament::tabs.item @click="activeTab = 'synced'" x-bind:active="activeTab === 'synced'">
            <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                <x-filament::icon icon="heroicon-m-check-circle" class="h-5 w-5 outline-none" />
                <span class="font-semibold">Synced History</span>
            </div>
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{-- ── Empty State ── --}}
    <div x-show="!loading && filteredDrafts.length === 0" x-transition style="display: none;" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 dark:border-gray-700 dark:bg-gray-900 shadow-sm mt-4">
        <x-filament::icon icon="heroicon-o-inbox" class="h-16 w-16 text-gray-400 mb-4 outline-none" />
        <h3 class="text-xl font-bold text-gray-950 dark:text-white" x-text="`No ${activeTab} records`"></h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm text-center">There are currently no records in this bucket. You're all caught up!</p>
    </div>

    {{-- ── Loading Skeleton ── --}}
    <div x-show="loading" class="space-y-4 mt-6">
        <template x-for="i in 3">
            <div class="h-32 animate-pulse rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
        </template>
    </div>

    {{-- ── Draft List ── --}}
    <div x-show="!loading && filteredDrafts.length > 0" class="space-y-4 mt-4">
        <template x-for="draft in filteredDrafts" :key="draft.local_uuid">
            <x-filament::section compact class="ring-1 ring-gray-950/5 dark:ring-white/10 shadow-md">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between py-1">
                    
                    {{-- Left side info --}}
                    <div class="flex flex-col gap-2 flex-wrap min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset"
                                  :class="{
                                      'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20': draft._outbox_status === 'pending',
                                      'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20': draft._outbox_status === 'syncing',
                                      'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20': draft._outbox_status === 'failed' || draft._outbox_status === 'error_needs_fix',
                                      'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20': draft._outbox_status === 'synced',
                                  }"
                                  x-text="draft._outbox_status === 'error_needs_fix' ? 'Needs Fix' : (draft._outbox_status.charAt(0).toUpperCase() + draft._outbox_status.slice(1))">
                            </span>
                            <span class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" x-text="formatType(draft.engagement_type)"></span>
                        </div>
                        
                        <h4 class="text-xl font-bold truncate text-gray-950 dark:text-white" x-text="draft.citizen_name || draft.stakeholder_partner || draft.organization_type || '—'"></h4>
                        
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            <x-filament::icon icon="heroicon-m-calendar" class="inline h-4 w-4 mr-1 text-gray-400 outline-none" />
                            <span x-text="formatDate(draft.created_at_mobile)"></span> 
                            <span class="mx-2 text-gray-300">|</span>
                            <x-filament::icon icon="heroicon-m-identification" class="inline h-4 w-4 mr-1 text-gray-400 outline-none" />
                            <span x-text="draft.local_uuid.slice(0,8) + '...'"></span>
                        </p>

                        <div x-show="draft._sync_error" style="display: none;" class="mt-2 rounded-lg bg-danger-50 dark:bg-danger-900/40 px-3 py-2 border border-danger-200 dark:border-danger-800">
                            <span class="text-sm font-semibold text-danger-700 dark:text-danger-400">
                                <x-filament::icon icon="heroicon-m-exclamation-circle" class="inline h-4 w-4 align-text-bottom mr-1 outline-none" />
                                Sync Error: <span x-text="draft._sync_error"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Right side actions --}}
                    <div class="flex items-center gap-3 shrink-0 mt-2 sm:mt-0">
                        <x-filament::button 
                            x-show="draft._outbox_status === 'pending' || draft._outbox_status === 'error_needs_fix' || draft._outbox_status === 'failed'" 
                            @click="openEditModal(draft)"
                            color="gray"
                            icon="heroicon-m-pencil-square"
                            size="sm">
                            Edit / Fix
                        </x-filament::button>

                        <x-filament::button 
                            x-show="draft._outbox_status === 'failed'" 
                            @click="retryDraft(draft)"
                            color="warning"
                            icon="heroicon-m-arrow-path"
                            size="sm">
                            Retry
                        </x-filament::button>

                        <x-filament::button 
                            x-show="draft._outbox_status !== 'syncing'" 
                            @click="confirmDelete(draft)"
                            color="danger"
                            icon="heroicon-m-trash"
                            size="sm">
                            Delete
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </template>
    </div>

    {{-- ── Edit Draft Modal ── --}}
    <x-filament::modal id="edit-draft-modal" display-classes="block" @close="editModal.open = false" width="lg" alignment="center">
        <x-slot name="heading">Fix Offline Draft</x-slot>
        <x-slot name="description">Correct the validation errors before retrying sync.</x-slot>

        <div class="space-y-6 pt-4" x-show="editModal.draft" x-transition>
            <template x-if="editModal.draft">
                <div class="space-y-4">
                    <div x-show="editModal.draft.engagement_type === 'house_to_house'" class="space-y-2">
                        <label class="block text-sm font-semibold leading-6 text-gray-950 dark:text-white">Citizen Name <span class="text-danger-600">*</span></label>
                        <input type="text" x-model="editModal.draft.citizen_name" class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold leading-6 text-gray-950 dark:text-white">Violation Type <span class="text-danger-600">*</span></label>
                        <select x-model="editModal.draft.violation_type" class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500">
                            <option value="">Select Type</option>
                            <option value="illegal_land_invasion">በህገ-ወጥ መሬት ወረራ</option>
                            <option value="illegal_construction">በህገ-ወጥ ግንባታ</option>
                            <option value="illegal_expansion">በህገ-ወጥ ማስፋፋት</option>
                            <option value="illegal_waste_disposal">በህገ-ወጥ ደረቅ እና ፍሳሽ ማስወገድ</option>
                            <option value="road_safety">መንገድ ደህንነት</option>
                            <option value="illegal_trade">በህገ-ወጥ ንግድ</option>
                            <option value="illegal_animal_trade">በህገ-ወጥ የእንስሳት ዝውውር/ዕርድ</option>
                            <option value="disturbing_acts">በአዋኪ ድርጊት</option>
                            <option value="illegal_advertisement">በህገ-ወጥ ማስታወቂያ</option>
                            <option value="none">ምንም ጥሰት የለም / None</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold leading-6 text-gray-950 dark:text-white">Block Number</label>
                        <input type="text" x-model="editModal.draft.block_number" class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500">
                    </div>
                </div>
            </template>
        </div>

        <x-slot name="footer">
            <div class="flex flex-row-reverse gap-3">
                <x-filament::button color="primary" @click="saveDraftEdits()">Save & Ready to Sync</x-filament::button>
                <x-filament::button color="gray" @click="$dispatch('close-modal', { id: 'edit-draft-modal' })">Cancel</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ── Delete Confirm Modal ── --}}
    <x-filament::modal id="delete-draft-modal" display-classes="block" @close="deleteModal.open = false" width="sm" alignment="center">
        <x-slot name="heading">Delete this offline draft?</x-slot>
        <x-slot name="description">This record has not been synced to the server. Deleting it will permanently remove it from your device.</x-slot>
        <x-slot name="footer">
            <div class="flex flex-row-reverse gap-3 pt-4">
                <x-filament::button color="danger" @click="deleteDraft()">Yes, Delete</x-filament::button>
                <x-filament::button color="gray" @click="$dispatch('close-modal', { id: 'delete-draft-modal' })">Cancel</x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
</div>

{{-- ── Alpine Component ── --}}
<script>
function denbOutbox() {
    return {
        drafts:        [],
        loading:       true,
        syncing:       false,
        refreshing:    false,
        isOnline:      navigator.onLine,
        syncProgress:  { synced: 0, failed: 0, total: 0 },
        deleteModal:   { open: false, draft: null },
        editModal:     { open: false, draft: null },
        activeTab:     'pending',

        // Idle Session Lock System
        idleLock: {
            isLocked: false,
            setupMode: false,
            pin: '',
            inputPin: '',
            error: false,
        },

        get filteredDrafts() {
            if (this.activeTab === 'pending') {
                return this.drafts.filter(d => ['pending', 'syncing'].includes(d._outbox_status));
            } else if (this.activeTab === 'failed') {
                return this.drafts.filter(d => ['failed', 'error_needs_fix'].includes(d._outbox_status));
            } else {
                return this.drafts.filter(d => d._outbox_status === 'synced');
            }
        },

        get progressState() {
            return {
                pending: this.drafts.filter(d => ['pending', 'syncing'].includes(d._outbox_status)).length,
                failed:  this.drafts.filter(d => ['failed', 'error_needs_fix'].includes(d._outbox_status)).length,
                synced:  this.drafts.filter(d => d._outbox_status === 'synced').length,
            };
        },

        async init() {
            window.addEventListener('online',  () => { this.isOnline = true; });
            window.addEventListener('offline', () => { this.isOnline = false; });
            window.addEventListener('denb:sync-complete', () => this.loadDrafts());
            
            this.initSessionLock();
            await this.loadDrafts();
        },

        initSessionLock() {
            this.idleLock.pin = localStorage.getItem('denb_offline_pin');
            if (this.idleLock.pin === null) {
                this.idleLock.setupMode = true;
            }

            const updateActivity = () => {
                if (!this.idleLock.isLocked && !this.idleLock.setupMode) {
                    localStorage.setItem('denb_last_activity', Date.now());
                }
            };
            window.addEventListener('touchstart', updateActivity);
            window.addEventListener('click', updateActivity);
            window.addEventListener('keydown', updateActivity);
            updateActivity();

            setInterval(() => {
                if (this.idleLock.isLocked || this.idleLock.setupMode || !this.idleLock.pin) return;
                const lastActivity = localStorage.getItem('denb_last_activity');
                // Lock if offline AND idle for > 2 hours (7200000ms), wait for demo I'll change to 2 hours
                if (lastActivity && !this.isOnline) {
                    if (Date.now() - parseInt(lastActivity) > 7200000) {
                        this.idleLock.isLocked = true;
                    }
                }
            }, 30000);
        },

        saveSetPin() {
            if (this.idleLock.inputPin.length >= 4) {
                localStorage.setItem('denb_offline_pin', this.idleLock.inputPin);
                this.idleLock.pin = this.idleLock.inputPin;
                this.idleLock.setupMode = false;
                this.idleLock.inputPin = '';
                window.DenbUI && window.DenbUI.showToast('Offline PIN Activated', 'success');
            }
        },

        skipPin() {
            localStorage.setItem('denb_offline_pin', ''); // empty string means skipped
            this.idleLock.pin = '';
            this.idleLock.setupMode = false;
        },

        unlockPin() {
            if (this.idleLock.inputPin === this.idleLock.pin) {
                this.idleLock.isLocked = false;
                this.idleLock.inputPin = '';
                this.idleLock.error = false;
                localStorage.setItem('denb_last_activity', Date.now());
            } else {
                this.idleLock.error = true;
                setTimeout(() => this.idleLock.error = false, 2000);
            }
        },

        async loadDrafts() {
            this.loading = true;
            try {
                if (window.DenbDB) {
                    this.drafts = await window.DenbDB.getAllDrafts();
                    // Auto switch to Fix Needed if there are failed items
                    if (this.progressState.failed > 0 && this.activeTab === 'pending' && this.progressState.pending === 0) {
                        this.activeTab = 'failed';
                    }
                }
            } catch (e) {
                console.error('Outbox load error:', e);
            } finally {
                this.loading = false;
            }
        },

        async syncAll() {
            if (!window.DenbSync || this.syncing) return;
            this.syncing = true;
            this.syncProgress = { synced: 0, failed: 0, total: this.progressState.pending };
            try {
                await window.DenbSync.syncOutbox({
                    onProgress: (p) => { this.syncProgress = p; }
                });
                await this.loadDrafts();
                if (window.DenbUI) {
                    if (this.progressState.failed > 0) {
                        window.DenbUI.showToast('Sync finished with some errors.', 'warning');
                    } else {
                        window.DenbUI.showToast('Sync complete ✓', 'success');
                    }
                }
            } catch (e) {
                window.DenbUI && window.DenbUI.showToast('Sync failed — check connection', 'error');
            } finally {
                this.syncing = false;
            }
        },

        async refreshMasterData() {
            if (!window.DenbSync || this.refreshing) return;
            this.refreshing = true;
            try {
                await window.DenbSync.refreshMasterData();
                window.DenbUI && window.DenbUI.showToast('Master data refreshed ✓', 'success');
            } catch (e) {
                window.DenbUI && window.DenbUI.showToast('Refresh failed', 'error');
            } finally {
                this.refreshing = false;
            }
        },

        openEditModal(draft) {
            // Create a deep copy for editing
            this.editModal.draft = JSON.parse(JSON.stringify(draft));
            window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'edit-draft-modal' } }));
        },

        async saveDraftEdits() {
            if (!window.DenbDB || !this.editModal.draft) return;
            
            // Basic required check
            if (this.editModal.draft.engagement_type === 'house_to_house' && !this.editModal.draft.citizen_name) {
                alert("Citizen name is required.");
                return;
            }

            try {
                // Update in indexedDB
                const updated = this.editModal.draft;
                updated._outbox_status = 'pending'; // Reset status to pending so it syncs again
                updated._sync_error = null; // Clear old error
                
                await window.DenbDB.saveEngagement(updated);
                
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'edit-draft-modal' } }));
                this.editModal.draft = null;
                await this.loadDrafts();
                window.DenbUI && window.DenbUI.showToast('Draft updated and ready to sync', 'success');
            } catch (e) {
                console.error(e);
            }
        },

        confirmDelete(draft) {
            this.deleteModal = { open: false, draft };
            window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'delete-draft-modal' } }));
        },

        async deleteDraft() {
            if (!this.deleteModal.draft || !window.DenbDB) return;
            await window.DenbDB.deleteDraft(this.deleteModal.draft.local_uuid);
            window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'delete-draft-modal' } }));
            this.deleteModal = { open: false, draft: null };
            await this.loadDrafts();
            window.DenbUI && window.DenbUI.showToast('Draft deleted', 'info');
        },

        async retryDraft(draft) {
            if (!window.DenbDB) return;
            await window.DenbDB.updateDraftStatus(draft.local_uuid, 'pending');
            await this.loadDrafts();
            this.syncAll();
        },

        formatType(type) {
            const map = {
                house_to_house:  'ቤት ለቤት — House to House',
                coffee_ceremony: 'ቡና ጠጡ — Coffee Ceremony',
                organization:    'በአደረጃጀት — Organization',
            };
            return map[type] || type;
        },

        formatDate(iso) {
            if (!iso) return '—';
            try {
                return new Intl.DateTimeFormat('am-ET', {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                }).format(new Date(iso));
            } catch (e) {
                return iso;
            }
        },
    };
}
</script>
</div>

</x-filament-panels::page>
