

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.renderProductDataMatrix = async (canvas, value) => {
    const bwipjs = await import('bwip-js/browser');

    bwipjs.toCanvas(canvas, {
        bcid: 'datamatrix',
        text: value,
        scale: 6,
        padding: 8,
        backgroundcolor: 'FFFFFF',
    });
};

Alpine.data('cartState', (initialItems) => ({
    items: Object.fromEntries(initialItems.map((item) => [item.id, item])),
    processing: {},
    message: '',
    messageIsError: false,

    get totalItems() {
        return Object.values(this.items).reduce((total, item) => total + item.quantity, 0);
    },

    get subtotal() {
        return Object.values(this.items).reduce((total, item) => total + (Number(item.price) * item.quantity), 0);
    },

    itemTotal(productId) {
        const item = this.items[productId];
        return `₱${(Number(item.price) * item.quantity).toFixed(2)}`;
    },

    async addItem(event, productId) {
        const form = event.currentTarget;
        if (this.processing[productId]) {
            return;
        }

        this.processing[productId] = true;
        this.message = '';
        this.messageIsError = false;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Could not update the cart.');
            }

            this.items = Object.fromEntries(result.cart.map((item) => [item.id, item]));
            window.dispatchEvent(new CustomEvent('cart-updated', {
                detail: { total: Number(result.subtotal) },
            }));
        } catch (error) {
            this.message = error instanceof Error ? error.message : 'Could not update the cart.';
            this.messageIsError = true;
        } finally {
            this.processing[productId] = false;
        }
    },
}));

Alpine.data('barcodeScanner', () => ({
    controls: null,
    cameraOpen: false,
    submitting: false,
    scanState: 'idle',
    scanStatus: '',
    noBarcodeTimer: null,

    async startCamera() {
        this.scanState = 'starting';
        this.scanStatus = 'Starting camera...';

        if (!navigator.mediaDevices?.getUserMedia) {
            this.scanState = 'error';
            this.scanStatus = 'Camera scanning requires a supported browser on HTTPS or localhost.';
            return;
        }

        this.cameraOpen = true;

        try {
            const { BrowserMultiFormatReader } = await import('@zxing/browser');
            const reader = new BrowserMultiFormatReader();
            const controls = await reader.decodeFromVideoDevice(undefined, this.$refs.video, (result) => {
                if (result) {
                    this.submitBarcode(result.getText());
                }
            });
            if (!this.cameraOpen || this.submitting) {
                controls.stop();
                return;
            }
            this.controls = controls;
            this.scanState = 'scanning';
            this.scanStatus = 'Camera is active. Point it at a product barcode.';
            this.noBarcodeTimer = window.setTimeout(() => {
                if (this.scanState === 'scanning') {
                    this.scanState = 'not-found';
                    this.scanStatus = 'No barcode detected yet. Adjust the camera and keep the barcode in view.';
                }
            }, 4000);
        } catch (error) {
            this.cameraOpen = false;
            this.scanState = 'error';
            this.scanStatus = error instanceof Error
                ? `Could not start the camera: ${error.message}`
                : 'Could not start the camera.';
        }
    },

    stopCamera() {
        window.clearTimeout(this.noBarcodeTimer);
        this.noBarcodeTimer = null;
        this.controls?.stop();
        this.controls = null;
        this.cameraOpen = false;
        if (!this.submitting && this.scanState !== 'error') {
            this.scanState = 'idle';
            this.scanStatus = 'Camera stopped.';
        }
    },

    submitBarcode(value) {
        if (this.submitting) {
            return;
        }

        this.submitting = true;
        this.scanState = 'detected';
        this.scanStatus = `Barcode detected (${value}). Adding product to cart...`;
        this.$refs.barcode.value = value;
        this.stopCamera();
        this.$refs.form.requestSubmit();
    },

    destroy() {
        window.clearTimeout(this.noBarcodeTimer);
        this.stopCamera();
    },
}));

Alpine.start();
