<x-filament-panels::page>

{{-- ══════════════════════════════════════════════════════════════════════════
     OUTBOX PAGE — Client-side rendered via Alpine.js + IndexedDB
     ══════════════════════════════════════════════════════════════════════════ --}}

<div
    x-data="denbOutbox()"
    x-init="init()"
    class="space-y-6 relative"
>
    {{-- ── Session Lock Overlay ── --}}
    <div x-show="idleLock.isLocked || idleLock.setupMode" 
         class="absolute inset-0 z-[100] flex items-center justify-center bg-gray-950 px-4 min-h-[500px] rounded-xl"
         x-transition>
        <div class="w-full max-w-sm rounded-2xl border border-gray-800 bg-gray-900 p-8 shadow-2xl text-center">
            <template x-if="idleLock.setupMode">
                <div>
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-950 text-primary-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Setup Offline PIN</h3>
                    <p class="text-xs text-gray-400 mb-6">Create a PIN to protect citizen data when your device is offline and idle.</p>
                    <input type="password" x-model="idleLock.inputPin" placeholder="Enter 4-digit PIN" class="px-4 py-3 w-full rounded-lg bg-gray-800 border-gray-700 text-white text-center tracking-[0.5em] font-bold text-xl focus:ring-primary-500 mb-4" maxlength="4">
                    <button @click="saveSetPin()" class="w-full rounded-lg bg-primary-600 px-4 py-3 font-semibold text-white hover:bg-primary-500">Set PIN</button>
                    <button @click="skipPin()" class="w-full mt-3 text-xs text-gray-500 hover:text-gray-300">Skip for now</button>
                </div>
            </template>
            <template x-if="idleLock.isLocked && !idleLock.setupMode">
                <div>
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-800 text-gray-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Session Locked</h3>
                    <p class="text-xs text-gray-400 mb-6">You have been offline and idle for 2 hours. Enter PIN to unlock Outbox.</p>
                    <input type="password" x-model="idleLock.inputPin" placeholder="****" class="px-4 py-3 w-full rounded-lg bg-gray-800 border-gray-700 text-white text-center tracking-[0.5em] font-bold text-2xl focus:ring-primary-500 mb-4" maxlength="4">
                    <button @click="unlockPin()" class="w-full rounded-lg bg-primary-600 px-4 py-3 font-semibold text-white hover:bg-primary-500">Unlock</button>
                    <p x-show="idleLock.error" class="text-xs text-red-500 mt-3 font-medium">Incorrect PIN</p>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Header Bar ── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">

        {{-- Status badge --}}
        <div class="flex items-center gap-3">
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                :class="{
                    'bg-green-950 text-green-300 ring-green-700':  isOnline,
                    'bg-red-950   text-red-300   ring-red-700':    !isOnline
                }"
            >
                <span
                    class="h-2 w-2 rounded-full"
                    :class="isOnline ? 'bg-green-400' : 'bg-red-400 animate-pulse'"
                ></span>
                <span x-text="isOnline ? 'Online' : 'Offline'"></span>
            </span>

            <span class="text-sm text-gray-400">
                <span x-text="drafts.length"></span> record(s) on device
            </span>
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-2">
            <button
                @click="refreshMasterData()"
                :disabled="!isOnline || refreshing"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
                <svg class="h-4 w-4" :class="refreshing && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="hidden sm:inline" x-text="refreshing ? 'Refreshing…' : 'Refresh Master Data'"></span>
            </button>

            <button
                @click="syncAll()"
                :disabled="!isOnline || syncing || progressState.pending === 0"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
                <svg class="h-4 w-4" :class="syncing && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span x-text="syncing ? 'Syncing…' : 'Sync All Now'"></span>
            </button>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="border-b border-gray-700">
        <nav class="-mb-px flex space-x-6">
            <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'border-primary-500 text-primary-400' : 'border-transparent text-gray-400 hover:border-gray-500 hover:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium flex items-center gap-2">
                Pending Sync
                <span x-show="progressState.pending > 0" class="rounded-full bg-primary-900 px-2 py-0.5 text-xs text-primary-300" x-text="progressState.pending"></span>
            </button>
            <button @click="activeTab = 'failed'" :class="activeTab === 'failed' ? 'border-danger-500 text-danger-400' : 'border-transparent text-gray-400 hover:border-gray-500 hover:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium flex items-center gap-2">
                Fix Needed
                <span x-show="progressState.failed > 0" class="rounded-full bg-danger-900 px-2 py-0.5 text-xs text-danger-300" x-text="progressState.failed"></span>
            </button>
            <button @click="activeTab = 'synced'" :class="activeTab === 'synced' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400 hover:border-gray-500 hover:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium flex items-center gap-2">
                Synced History
            </button>
        </nav>
    </div>

    {{-- ── Progress Bar (during sync) ── --}}
    <div x-show="syncing" x-transition class="rounded-lg bg-primary-950 border border-primary-800 p-3">
        <div class="flex justify-between text-xs text-primary-300 mb-2">
            <span>Uploading records…</span>
            <span x-text="`${syncProgress.synced} / ${syncProgress.total}`"></span>
        </div>
        <div class="h-2 w-full rounded-full bg-primary-900">
            <div
                class="h-2 rounded-full bg-primary-400 transition-all duration-300"
                :style="`width: ${syncProgress.total > 0 ? (syncProgress.synced / syncProgress.total * 100) : 0}%`"
            ></div>
        </div>
    </div>

    {{-- ── Empty State ── --}}
    <div x-show="!loading && filteredDrafts.length === 0" x-transition class="rounded-xl border border-dashed border-gray-700 bg-gray-900 p-10 text-center">
        <div class="text-4xl mb-3">📭</div>
        <p class="text-base font-semibold text-gray-300" x-text="`No ${activeTab} records`"></p>
        <p class="mt-1 text-sm text-gray-500">You're all caught up here.</p>
    </div>

    {{-- ── Loading Skeleton ── --}}
    <div x-show="loading" class="space-y-3">
        <template x-for="i in 3">
            <div class="h-20 animate-pulse rounded-xl bg-gray-800"></div>
        </template>
    </div>

    {{-- ── Draft List ── --}}
    <div x-show="!loading && filteredDrafts.length > 0" class="space-y-3">
        <template x-for="draft in filteredDrafts" :key="draft.local_uuid">
            <div
                class="rounded-xl border p-4 transition outline-none"
                :tabindex="draft._outbox_status === 'error_needs_fix' ? 0 : -1"
                :class="{
                    'border-gray-700 bg-gray-900':              draft._outbox_status === 'pending',
                    'border-primary-700 bg-primary-950':        draft._outbox_status === 'syncing',
                    'border-danger-700   bg-danger-950':        draft._outbox_status === 'failed' || draft._outbox_status === 'error_needs_fix',
                    'border-gray-600  bg-gray-800 opacity-60':  draft._outbox_status === 'synced',
                    'ring-2 ring-danger-500':                   draft._outbox_status === 'error_needs_fix',
                }"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    {{-- Main info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Status badge --}}
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                                :class="{
                                    'bg-yellow-950 text-yellow-300 ring-yellow-700': draft._outbox_status === 'pending',
                                    'bg-blue-950   text-blue-300   ring-blue-700':   draft._outbox_status === 'syncing',
                                    'bg-green-950  text-green-300  ring-green-700':  draft._outbox_status === 'synced',
                                    'bg-red-950    text-red-300    ring-red-700':    draft._outbox_status === 'failed' || draft._outbox_status === 'error_needs_fix',
                                }"
                                x-text="draft._outbox_status === 'error_needs_fix' ? 'Needs Fix' : (draft._outbox_status.charAt(0).toUpperCase() + draft._outbox_status.slice(1))"
                            ></span>

                            {{-- Engagement type --}}
                            <span class="text-xs font-medium text-gray-400" x-text="formatType(draft.engagement_type)"></span>
                        </div>

                        <p class="mt-2 text-sm font-bold text-gray-200 truncate"
                           x-text="draft.citizen_name || draft.stakeholder_partner || draft.organization_type || '—'">
                        </p>

                        {{-- Sync Error display --}}
                        <div x-show="draft._sync_error" class="mt-2 rounded bg-danger-900/50 px-3 py-2 text-sm text-danger-200 border border-danger-800">
                            <strong>Sync Error:</strong> <span x-text="draft._sync_error"></span>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">
                            🕐 <span x-text="formatDate(draft.created_at_mobile)"></span>
                            &nbsp;|&nbsp;
                            🪪 <span x-text="draft.local_uuid ? draft.local_uuid.slice(0,8) + '…' : '—'"></span>
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Edit draft --}}
                        <button
                            x-show="draft._outbox_status === 'pending' || draft._outbox_status === 'error_needs_fix' || draft._outbox_status === 'failed'"
                            @click="openEditModal(draft)"
                            class="rounded-lg bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-600 transition"
                        >Edit / Fix</button>

                        {{-- Retry failed --}}
                        <button
                            x-show="draft._outbox_status === 'failed'"
                            @click="retryDraft(draft)"
                            class="rounded-lg bg-orange-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600 transition"
                        >Retry</button>

                        {{-- Delete draft --}}
                        <button
                            x-show="draft._outbox_status !== 'syncing'"
                            @click="confirmDelete(draft)"
                            class="rounded-lg bg-danger-900 px-3 py-1.5 text-xs font-semibold text-danger-300 hover:bg-danger-800 transition shadow-sm"
                        >Delete</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ── Edit Draft Modal ── --}}
    <div
        x-show="editModal.open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/80 p-4"
        @click.self="editModal.open = false"
    >
        <div class="w-full max-w-lg rounded-2xl border border-gray-700 bg-gray-900 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="border-b border-gray-800 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Fix Offline Draft</h3>
                <button @click="editModal.open = false" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto w-full space-y-4">
                {{-- Dynamic form fields based on draft data --}}
                <template x-if="editModal.draft">
                    <div class="space-y-4">
                        <div x-show="editModal.draft.engagement_type === 'house_to_house'">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Citizen Name <span class="text-danger-500">*</span></label>
                            <input type="text" x-model="editModal.draft.citizen_name" class="w-full rounded-lg border-gray-700 bg-gray-800 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Violation Type <span class="text-danger-500">*</span></label>
                            <select x-model="editModal.draft.violation_type" class="w-full rounded-lg border-gray-700 bg-gray-800 text-white focus:border-primary-500 focus:ring-primary-500">
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

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Block Number</label>
                            <input type="text" x-model="editModal.draft.block_number" class="w-full rounded-lg border-gray-700 bg-gray-800 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t border-gray-800 px-6 py-4 flex justify-end gap-3 bg-gray-900">
                <button @click="editModal.open = false" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white">Cancel</button>
                <button @click="saveDraftEdits()" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500 shadow-sm">Save & Ready to Sync</button>
            </div>
        </div>
    </div>

    {{-- ── Delete Confirm Modal ── --}}
    <div
        x-show="deleteModal.open"
        x-transition
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 p-4"
        @click.self="deleteModal.open = false"
    >
        <div class="w-full max-w-sm rounded-2xl border border-gray-700 bg-gray-900 p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white">Delete Draft?</h3>
            <p class="mt-1 text-sm text-gray-400">
                This record has <strong class="text-danger-400">not been synced</strong>.
                Deleting it will permanently remove it from your device.
            </p>
            <div class="mt-4 flex justify-end gap-3">
                <button @click="deleteModal.open = false"
                    class="rounded-lg bg-gray-700 px-4 py-2 text-sm text-gray-200 hover:bg-gray-600">
                    Cancel
                </button>
                <button @click="deleteDraft()"
                    class="rounded-lg bg-danger-700 px-4 py-2 text-sm font-semibold text-white hover:bg-danger-600 shadow-sm">
                    Yes, Delete
                </button>
            </div>
        </div>
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
            this.editModal.open = true;
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
                
                this.editModal.open = false;
                this.editModal.draft = null;
                await this.loadDrafts();
                window.DenbUI && window.DenbUI.showToast('Draft updated and ready to sync', 'success');
            } catch (e) {
                console.error(e);
            }
        },

        confirmDelete(draft) {
            this.deleteModal = { open: true, draft };
        },

        async deleteDraft() {
            if (!this.deleteModal.draft || !window.DenbDB) return;
            await window.DenbDB.deleteDraft(this.deleteModal.draft.local_uuid);
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

</x-filament-panels::page>
