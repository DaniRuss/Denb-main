<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            isDrawing: false,
            ctx: null,

            init() {
                const canvas = this.$refs.canvas;
                const rect = canvas.parentElement.getBoundingClientRect();
                canvas.width = rect.width || 300;
                canvas.height = 180;

                this.ctx = canvas.getContext('2d');
                this.ctx.lineWidth = 2.5;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';
                this.ctx.strokeStyle = '#1e293b'; // Slate 800

                window.addEventListener('resize', () => {
                    const rect = canvas.parentElement.getBoundingClientRect();
                    const oldImg = this.state;
                    canvas.width = rect.width || 300;
                    this.ctx.lineWidth = 2.5;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                    this.ctx.strokeStyle = '#1e293b';
                    if (oldImg) {
                        const img = new Image();
                        img.onload = () => this.ctx.drawImage(img, 0, 0);
                        img.src = oldImg;
                    }
                });

                if (this.state) {
                    const img = new Image();
                    img.onload = () => this.ctx.drawImage(img, 0, 0);
                    img.src = this.state;
                }
            },

            getPos(e) {
                const rect = this.$refs.canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            },

            startDrawing(e) {
                this.isDrawing = true;
                const pos = this.getPos(e);
                this.ctx.beginPath();
                this.ctx.moveTo(pos.x, pos.y);
            },

            draw(e) {
                if (!this.isDrawing) return;
                const pos = this.getPos(e);
                this.ctx.lineTo(pos.x, pos.y);
                this.ctx.stroke();
            },

            stopDrawing() {
                if (this.isDrawing) {
                    this.isDrawing = false;
                    this.ctx.closePath();
                    this.save();
                }
            },

            clear() {
                const canvas = this.$refs.canvas;
                this.ctx.clearRect(0, 0, canvas.width, canvas.height);
                this.state = null;
            },

            save() {
                this.state = this.$refs.canvas.toDataURL('image/png');
            }
        }"
        class="relative border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 overflow-hidden shadow-inner transition-all hover:border-primary-400 focus-within:ring-2 focus-within:ring-primary-500/20"
    >
        {{-- Header / Badge --}}
        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <div class="h-1.5 w-1.5 rounded-full bg-primary-500 animate-pulse"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Legal Verification</span>
            </div>
            <span class="text-[10px] text-gray-400 italic">Signature strictly required</span>
        </div>

        <!-- Canvas for drawing -->
        <canvas
            x-ref="canvas"
            class="w-full h-[180px] cursor-crosshair touch-none bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:24px_24px] dark:bg-[radial-gradient(#374151_1px,transparent_1px)]"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
            @touchstart.prevent="startDrawing"
            @touchmove.prevent="draw"
            @touchend.prevent="stopDrawing"
        ></canvas>
        
        {{-- Footer Controls --}}
        <div class="flex items-center justify-between px-3 py-2 border-t border-gray-100 dark:border-gray-800">
            <p class="text-[11px] font-medium text-gray-400">
                <span class="hidden sm:inline">Please draw carefully within the lines</span>
                <span class="sm:hidden">Draw signature in the box</span>
            </p>
            <button
                type="button"
                @click="clear"
                class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-bold text-danger-600 bg-danger-50 hover:bg-danger-100 rounded-lg transition-colors"
            >
                <x-filament::icon icon="heroicon-m-trash" class="h-3 w-3" />
                Clear
            </button>
        </div>
    </div>
</x-dynamic-component>

