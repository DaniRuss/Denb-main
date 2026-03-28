<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            isProcessing: false,
            debugSize: 0,
            
            init() {
                if (this.state) {
                    this.debugSize = Math.round((this.state.length * 0.75) / 1024);
                }
            },
            
            handleFile(e) {
                const file = e.target.files[0];
                if (!file) return;

                if (file.size > 10 * 1024 * 1024) {
                    window.DenbUI && window.DenbUI.showToast('File too large (Max 10MB)', 'error');
                    return;
                }

                this.isProcessing = true;
                const reader = new FileReader();

                reader.onload = (event) => {
                    const img = new Image();
                    img.onload = () => {
                        const MAX_WIDTH = 1280;
                        let width = img.width;
                        let height = img.height;

                        if (width > MAX_WIDTH) {
                            height = Math.round(height * (MAX_WIDTH / width));
                            width = MAX_WIDTH;
                        }

                        const canvas = this.$refs.canvas;
                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // High quality-to-size ratio
                        const base64 = canvas.toDataURL('image/jpeg', 0.7);
                        
                        this.state = base64;
                        this.debugSize = Math.round((base64.length * 0.75) / 1024);
                        this.isProcessing = false;
                        
                        ctx.clearRect(0, 0, width, height);
                        canvas.width = 0;
                        canvas.height = 0;
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
                e.target.value = '';
            },

            clear() {
                this.state = null;
                this.debugSize = 0;
            }
        }"
        class="relative border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800/20 p-4 transition-all hover:bg-gray-100 dark:hover:bg-gray-800/40"
    >
        <div class="flex items-center justify-between gap-4">
            <canvas x-ref="canvas" class="hidden"></canvas>
            
            <div class="flex-1">
                <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-6 py-4 transition-all hover:border-primary-500 hover:bg-primary-50">
                    <div class="flex flex-col items-center gap-1 text-center">
                        <div class="rounded-full bg-primary-100 p-2 text-primary-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                            <x-filament::icon icon="heroicon-m-camera" class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-tight">Capture Evidence</span>
                        <p class="text-[10px] text-gray-400">Tap to take photo or upload</p>
                    </div>
                    
                    <input
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="sr-only"
                        @change="handleFile"
                        :disabled="isProcessing"
                    >
                </label>
            </div>

            <!-- Loading Indicator -->
            <div x-show="isProcessing" class="shrink-0 flex items-center justify-center p-4">
                 <div class="flex flex-col items-center gap-1">
                    <svg class="w-6 h-6 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-[10px] font-bold text-primary-600">Processing...</span>
                 </div>
            </div>
        </div>

        <!-- Preview Area -->
        <template x-if="state">
            <div class="mt-4 flex animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="relative group">
                    <img :src="state" class="h-40 w-full max-w-[240px] rounded-xl object-cover shadow-lg ring-1 ring-black/5 dark:ring-white/10">
                    
                    {{-- Overlay metadata --}}
                    <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between rounded-lg bg-black/40 px-2 py-1 backdrop-blur-md opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="text-[9px] font-bold text-white uppercase tracking-tighter" x-text="debugSize + ' KB Optimised'"></span>
                    </div>

                    <button
                        type="button"
                        @click="clear"
                        class="absolute -top-3 -right-3 flex h-7 w-7 items-center justify-center rounded-full bg-white dark:bg-gray-850 text-danger-600 shadow-xl ring-1 ring-gray-950/10 hover:bg-danger-50 active:scale-95 transition-all"
                    >
                        <x-filament::icon icon="heroicon-m-x-circle" class="h-5 w-5" />
                    </button>
                 </div>
            </div>
        </template>
    </div>
</x-dynamic-component>

