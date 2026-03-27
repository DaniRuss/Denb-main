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
                canvas.height = 160;

                this.ctx = canvas.getContext('2d');
                this.ctx.lineWidth = 2;
                this.ctx.lineCap = 'round';
                this.ctx.strokeStyle = '#000000';

                window.addEventListener('resize', () => {
                    const rect = canvas.parentElement.getBoundingClientRect();
                    const oldImg = this.state;
                    canvas.width = rect.width || 300;
                    this.ctx.lineWidth = 2;
                    this.ctx.lineCap = 'round';
                    this.ctx.strokeStyle = '#000000';
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
        class="border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm"
    >
        <!-- Canvas for drawing -->
        <canvas
            x-ref="canvas"
            class="w-full h-40 cursor-crosshair touch-none bg-gray-50 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
            @touchstart.prevent="startDrawing"
            @touchmove.prevent="draw"
            @touchend.prevent="stopDrawing"
        ></canvas>
        
        <!-- Controls -->
        <div class="flex items-center justify-between p-2 border-t border-gray-200 bg-gray-50">
            <span class="text-xs font-medium text-gray-500">Sign in the box above / ከላይ ባለው ሳጥን ውስጥ ይፈርሙ</span>
            <button
                type="button"
                @click="clear"
                class="px-3 py-1.5 text-xs font-semibold text-danger-600 bg-white ring-1 ring-danger-600/20 rounded hover:bg-danger-50 transition"
            >
                Clear Signature
            </button>
        </div>
    </div>
</x-dynamic-component>
