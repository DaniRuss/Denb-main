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
                    alert('File too large');
                    return;
                }

                this.isProcessing = true;
                const reader = new FileReader();

                reader.onload = (event) => {
                    const img = new Image();
                    img.onload = () => {
                        const MAX_WIDTH = 1024;
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

                        const base64 = canvas.toDataURL('image/jpeg', 0.6);
                        
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
        class="border border-gray-200 rounded-lg bg-gray-50/50 p-4"
    >
        <div class="flex items-center gap-4">
            <!-- Hidden Canvas for resizing -->
            <canvas x-ref="canvas" class="hidden"></canvas>
            
            <!-- File Input -->
            <label class="relative cursor-pointer font-semibold focus-within:outline-none">
                <span class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm shadow-sm ring-1 ring-gray-950/10 hover:bg-gray-50 text-gray-700">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Take Photo / Capture
                </span>
                <input
                    type="file"
                    accept="image/*"
                    capture="environment"
                    class="sr-only"
                    @change="handleFile"
                    :disabled="isProcessing"
                >
            </label>

            <!-- Loading State -->
            <div x-show="isProcessing" class="text-xs text-primary-600 flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Compressing...
            </div>
        </div>

        <!-- Preview -->
        <div x-show="state" class="mt-4 relative inline-block">
            <img :src="state" class="h-32 rounded-lg object-cover shadow-sm ring-1 ring-gray-950/10">
            <button
                type="button"
                @click="clear"
                class="absolute -top-2 -right-2 bg-danger-600 text-white rounded-full p-1 shadow-md hover:bg-danger-500"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="text-xs text-gray-500 mt-1">Compressed size: <span x-text="debugSize"></span> KB</div>
        </div>
    </div>
</x-dynamic-component>
