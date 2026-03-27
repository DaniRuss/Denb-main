<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="signaturePad({ state: $wire.$entangle('{{ $getStatePath() }}') })"
        class="border rounded-lg bg-white overflow-hidden"
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
        <div class="flex items-center justify-between p-2 border-t bg-gray-100">
            <span class="text-xs text-gray-500">Sign in the box above</span>
            <button
                type="button"
                @click="clear"
                class="px-3 py-1 text-xs font-semibold text-red-600 bg-red-100 rounded hover:bg-red-200"
            >
                Clear Signature
            </button>
        </div>
    </div>

    <script>
        function signaturePad(config) {
            return {
                state: config.state,
                isDrawing: false,
                ctx: null,

                init() {
                    const canvas = this.$refs.canvas;
                    // Fix high-DPI blur
                    const rect = canvas.parentElement.getBoundingClientRect();
                    canvas.width = rect.width;
                    canvas.height = 160;

                    this.ctx = canvas.getContext('2d');
                    this.ctx.lineWidth = 2;
                    this.ctx.lineCap = 'round';
                    this.ctx.strokeStyle = '#000000';

                    // If existing state, try to render it (useful for Edit Drafts later)
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
                    // Save as base64 PNG
                    this.state = this.$refs.canvas.toDataURL('image/png');
                }
            }
        }
    </script>
</x-dynamic-component>
