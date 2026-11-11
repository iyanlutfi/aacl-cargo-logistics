// AACL Cargo & Logistics — Alpine.js Helpers (Livewire 4 bundles Alpine automatically)

window.aaclPrintTable = function () {
    document.body.classList.remove('printing-cn');
    let styleEl = document.getElementById('aacl-print-page-style');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'aacl-print-page-style';
        document.head.appendChild(styleEl);
    }
    styleEl.textContent = '@page { size: A4 landscape; margin: 5mm; }';
    setTimeout(() => window.print(), 60);
};

window.aaclPrintCn = function () {
    document.body.classList.add('printing-cn');
    let styleEl = document.getElementById('aacl-print-page-style');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'aacl-print-page-style';
        document.head.appendChild(styleEl);
    }
    styleEl.textContent = '@page { size: A4 portrait; margin: 10mm; }';
    const cleanup = () => {
        document.body.classList.remove('printing-cn');
        window.removeEventListener('afterprint', cleanup);
    };
    window.addEventListener('afterprint', cleanup);
    setTimeout(() => window.print(), 60);
};

function registerAaclAlpineComponents() {
    if (!window.Alpine || window.__aaclAlpineRegistered) return;
    window.__aaclAlpineRegistered = true;

    Alpine.data('toastManager', () => ({
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            const message = typeof detail === 'string' ? detail : (detail?.message ?? 'Berhasil');
            const type = typeof detail === 'object' ? (detail?.type ?? 'success') : 'success';
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter((t) => t.id !== id);
            }, 3800);
        },
        remove(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },
    }));

    Alpine.data('dimensionCalc', () => ({
        open: false,
        lengthCm: '',
        widthCm: '',
        heightCm: '',
        qty: 1,
        get volM3() {
            const p = parseFloat(this.lengthCm) || 0;
            const l = parseFloat(this.widthCm) || 0;
            const t = parseFloat(this.heightCm) || 0;
            const q = Math.max(1, parseFloat(this.qty) || 1);
            return ((p * l * t) / 1000000) * q;
        },
        get areaM2() {
            const p = parseFloat(this.lengthCm) || 0;
            const l = parseFloat(this.widthCm) || 0;
            const q = Math.max(1, parseFloat(this.qty) || 1);
            return ((p * l) / 10000) * q;
        },
        get volWeightKg() {
            const p = parseFloat(this.lengthCm) || 0;
            const l = parseFloat(this.widthCm) || 0;
            const t = parseFloat(this.heightCm) || 0;
            const q = Math.max(1, parseFloat(this.qty) || 1);
            return ((p * l * t) / 4000) * q;
        },
        applyToWire(wire) {
            wire.form_volume_m3 = Number(this.volM3.toFixed(4));
            wire.form_area_m2 = Number(this.areaM2.toFixed(4));
            if (!wire.form_colly || Number(wire.form_colly) === 0) {
                wire.form_colly = Math.max(1, Math.round(parseFloat(this.qty) || 1));
            }
            if ((!wire.form_weight_kg || Number(wire.form_weight_kg) === 0) && this.volWeightKg > 0) {
                wire.form_weight_kg = Number(this.volWeightKg.toFixed(2));
            }
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Dimensi m³ & m² diterapkan ke form.', type: 'info' } }));
        },
    }));

    Alpine.data('tariffCalc', () => ({
        basis: 'manual',
        rate: '',
        extraFee: 0,
        computeTotal(colly, weightKg, volumeM3, areaM2) {
            const r = parseFloat(this.rate) || 0;
            const extra = parseFloat(this.extraFee) || 0;
            let qty = 0;
            if (this.basis === 'kg') qty = parseFloat(weightKg) || 0;
            else if (this.basis === 'volume') qty = parseFloat(volumeM3) || 0;
            else if (this.basis === 'colly') qty = parseFloat(colly) || 0;
            else if (this.basis === 'area') qty = parseFloat(areaM2) || 0;
            else return null;
            return Math.round(r * qty + extra);
        },
        formatRupiah(num) {
            const n = Math.round(parseFloat(num) || 0);
            return 'Rp ' + n.toLocaleString('id-ID');
        },
        applyToWire(wire) {
            if (this.basis === 'manual') return;
            const total = this.computeTotal(
                wire.form_colly,
                wire.form_weight_kg,
                wire.form_volume_m3,
                wire.form_area_m2
            );
            if (total !== null) {
                wire.form_service_cost = total;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Estimasi tarif diterapkan ke Harga Kirim.', type: 'info' } }));
            }
        },
    }));

    Alpine.data('signaturePad', (initialSig = null) => ({
        drawing: false,
        hasDrawn: Boolean(initialSig),
        ctx: null,
        init() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;
            this.ctx = canvas.getContext('2d');
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#0f2744';
            if (initialSig) {
                this.loadDataUrl(initialSig);
            }
        },
        loadDataUrl(dataUrl) {
            if (!dataUrl || !this.$refs.canvas) return;
            const img = new Image();
            img.onload = () => {
                const c = this.$refs.canvas;
                if (!c || !this.ctx) return;
                this.ctx.clearRect(0, 0, c.width, c.height);
                this.ctx.drawImage(img, 0, 0, c.width, c.height);
                this.hasDrawn = true;
            };
            img.src = dataUrl;
        },
        getPoint(e) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            const scaleX = this.$refs.canvas.width / rect.width;
            const scaleY = this.$refs.canvas.height / rect.height;
            const touch = e.touches?.[0] || e.changedTouches?.[0];
            const clientX = touch ? touch.clientX : e.clientX;
            const clientY = touch ? touch.clientY : e.clientY;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY,
            };
        },
        start(e) {
            if (!this.ctx) return;
            this.drawing = true;
            const p = this.getPoint(e);
            this.ctx.beginPath();
            this.ctx.moveTo(p.x, p.y);
            this.ctx.lineTo(p.x + 0.1, p.y + 0.1);
            this.ctx.stroke();
            this.hasDrawn = true;
        },
        move(e) {
            if (!this.drawing || !this.ctx) return;
            e.preventDefault();
            const p = this.getPoint(e);
            this.ctx.lineTo(p.x, p.y);
            this.ctx.stroke();
            this.hasDrawn = true;
        },
        end(wire) {
            if (!this.drawing) return;
            this.drawing = false;
            this.ctx?.closePath();
            if (this.hasDrawn && wire && this.$refs.canvas) {
                // Deferred client-side update (no roundtrip lag on each stroke)
                wire.pod_signature_data = this.$refs.canvas.toDataURL('image/png');
                wire.pod_clear_signature = false;
            }
        },
        clear(wire) {
            const c = this.$refs.canvas;
            if (this.ctx && c) {
                this.ctx.clearRect(0, 0, c.width, c.height);
            }
            this.hasDrawn = false;
            if (wire) {
                wire.pod_signature_data = '';
                wire.pod_clear_signature = true;
            }
        },
    }));
}

document.addEventListener('alpine:init', registerAaclAlpineComponents);
if (window.Alpine) {
    registerAaclAlpineComponents();
}
