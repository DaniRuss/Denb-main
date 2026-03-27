<x-filament-panels::page>

{{-- ══════════════════════════════════════════════════════════════════════════
     OUTBOX PAGE — Client-side rendered via Alpine.js + IndexedDB
     ══════════════════════════════════════════════════════════════════════════ --}}

<div
    x-data="denbOutbox()"
    x-init="init()"
    class="space-y-6"
>
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
                <span x-text="drafts.length"></span> record(s) in outbox
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
                <span x-text="refreshing ? 'Refreshing…' : 'Refresh Master Data'"></span>
            </button>

            <button
                @click="syncAll()"
                :disabled="!isOnline || syncing || drafts.filter(d => d._outbox_status === 'pending').length === 0"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
                <svg class="h-4 w-4" :class="syncing && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span x-text="syncing ? 'Syncing…' : 'Sync All Now'"></span>
            </button>
        </div>
    </div>

    {{-- ── Progress Bar (during sync) ── --}}
    <div x-show="syncing" x-transition class="rounded-lg bg-blue-950 border border-blue-800 p-3">
        <div class="flex justify-between text-xs text-blue-300 mb-2">
            <span>Uploading records…</span>
            <span x-text="`${syncProgress.synced} / ${syncProgress.total}`"></span>
        </div>
        <div class="h-2 w-full rounded-full bg-blue-900">
            <div
                class="h-2 rounded-full bg-blue-400 transition-all duration-300"
                :style="`width: ${syncProgress.total > 0 ? (syncProgress.synced / syncProgress.total * 100) : 0}%`"
            ></div>
        </div>
    </div>

    {{-- ── Empty State ── --}}
    <div x-show="!loading && drafts.length === 0" x-transition class="rounded-xl border border-dashed border-gray-700 bg-gray-900 p-10 text-center">
        <div class="text-4xl mb-3">📭</div>
        <p class="text-base font-semibold text-gray-300">Outbox is Empty</p>
        <p class="mt-1 text-sm text-gray-500">All records have been synced to the server.</p>
    </div>

    {{-- ── Loading Skeleton ── --}}
    <div x-show="loading" class="space-y-3">
        <template x-for="i in 3">
            <div class="h-20 animate-pulse rounded-xl bg-gray-800"></div>
        </template>
    </div>

    {{-- ── Draft List ── --}}
    <div x-show="!loading && drafts.length > 0" class="space-y-3">
        <template x-for="draft in drafts" :key="draft.local_uuid">
            <div
                class="rounded-xl border p-4 transition"
                :class="{
                    'border-gray-700 bg-gray-900':              draft._outbox_status === 'pending',
                    'border-green-700 bg-green-950':            draft._outbox_status === 'syncing',
                    'border-red-700   bg-red-950':              draft._outbox_status === 'failed',
                    'border-gray-600  bg-gray-800 opacity-60':  draft._outbox_status === 'synced',
                }"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">

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
                                    'bg-red-950    text-red-300    ring-red-700':    draft._outbox_status === 'failed',
                                }"
                                x-text="draft._outbox_status.charAt(0).toUpperCase() + draft._outbox_status.slice(1)"
                            ></span>

                            {{-- Engagement type --}}
                            <span class="text-xs font-medium text-gray-400" x-text="formatType(draft.engagement_type)"></span>
                        </div>

                        <p class="mt-1 text-sm font-semibold text-gray-200 truncate"
                           x-text="draft.citizen_name || draft.stakeholder_partner || draft.organization_type || '—'">
                        </p>

                        <p class="text-xs text-gray-500 mt-0.5">
                            🕐 <span x-text="formatDate(draft.created_at_mobile)"></span>
                            &nbsp;|&nbsp;
                            🪪 <span x-text="draft.local_uuid ? draft.local_uuid.slice(0,8) + '…' : '—'"></span>
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 shrink-0">
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
                            class="rounded-lg bg-red-900 px-3 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-800 transition"
                        >Delete</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ── Delete Confirm Modal ── --}}
    <div
        x-show="deleteModal.open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
        @click.self="deleteModal.open = false"
    >
        <div class="w-full max-w-sm rounded-2xl border border-gray-700 bg-gray-900 p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white">Delete Draft?</h3>
            <p class="mt-1 text-sm text-gray-400">
                This record has <strong class="text-red-300">not been synced</strong>.
                Deleting it will permanently remove it from your device.
            </p>
            <div class="mt-4 flex justify-end gap-3">
                <button @click="deleteModal.open = false"
                    class="rounded-lg bg-gray-700 px-4 py-2 text-sm text-gray-200 hover:bg-gray-600">
                    Cancel
                </button>
                <button @click="deleteDraft()"
                    class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">
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

        async init() {
            window.addEventListener('online',  () => { this.isOnline = true; });
            window.addEventListener('offline', () => { this.isOnline = false; });
            window.addEventListener('denb:sync-complete', () => this.loadDrafts());
            await this.loadDrafts();
        },

        async loadDrafts() {
            this.loading = true;
            try {
                // Use the global DenbDB exposed by offline-db.js, or fallback
                if (window.DenbDB) {
                    this.drafts = await window.DenbDB.getAllDrafts();
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
            this.syncProgress = { synced: 0, failed: 0, total: this.drafts.filter(d => d._outbox_status === 'pending').length };
            try {
                await window.DenbSync.syncOutbox({
                    onProgress: (p) => { this.syncProgress = p; }
                });
                await this.loadDrafts();
                window.DenbUI && window.DenbUI.showToast('Sync complete ✓', 'success');
            } catch (e) {
                window.DenbUI && window.DenbUI.showToast('Sync failed — try again', 'error');
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

        confirmDelete(draft) {
            this.deleteModal = { open: true, draft };
        },

        async deleteDraft() {
            if (!this.deleteModal.draft || !window.DenbDB) return;
            await window.DenbDB.deleteDraft(this.deleteModal.draft.local_uuid);
            this.deleteModal = { open: false, draft: null };
            await this.loadDrafts();
            window.DenbUI && window.DenbUI.showToast('Draft deleted', 'warning');
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
