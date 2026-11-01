import './bootstrap';
import { Html5Qrcode } from 'html5-qrcode';

window.Html5Qrcode = Html5Qrcode;

window.bookingCountdown = (endTime) => ({
	remaining: 0,
	timeout: null,

	start() {
		this.update();
		this.scheduleNextUpdate();
	},

	scheduleNextUpdate() {
		this.timeout = window.setTimeout(() => {
			this.update();
			this.scheduleNextUpdate();
		}, 1000 - (Date.now() % 1000));
	},

	update() {
		this.remaining = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
	},

	get formatted() {
		const hours = Math.floor(this.remaining / 3600);
		const minutes = Math.floor((this.remaining % 3600) / 60);
		const seconds = this.remaining % 60;

		return [hours, minutes, seconds].map((value) => String(value).padStart(2, '0')).join(':');
	},

	destroy() {
		window.clearTimeout(this.timeout);
	},
});

window.qrScanner = () => ({
	scanner: null,
	isStarting: false,
	isScanning: false,
	isSubmitting: false,
	feedback: '',
	feedbackType: 'info',

	async start() {
		this.feedback = '';
		this.isStarting = true;

		try {
			if (!window.isSecureContext) {
				throw new Error('Kamera memerlukan koneksi HTTPS atau localhost.');
			}

			this.scanner = new window.Html5Qrcode('qr-camera', { verbose: false });
			await this.scanner.start(
				{ facingMode: 'environment' },
				{ fps: 10, qrbox: { width: 240, height: 240 }, aspectRatio: 1 },
				(decodedText) => this.handleScan(decodedText),
				() => {},
			);

			this.isScanning = true;
			this.feedback = 'Kamera aktif. Arahkan ke QR unit.';
			this.feedbackType = 'info';
		} catch (error) {
			await this.releaseCamera();
			const message = String(error?.message || error);
			this.feedback = /NotAllowed|Permission|denied/i.test(message)
				? 'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini.'
				: /NotFound|device.*not found/i.test(message)
					? 'Kamera tidak ditemukan pada perangkat ini.'
					: message;
			this.feedbackType = 'error';
		} finally {
			this.isStarting = false;
		}
	},

	async handleScan(decodedText) {
		if (this.isSubmitting) return;
		this.isSubmitting = true;
		this.feedback = 'QR terbaca. Memvalidasi check-in...';
		this.feedbackType = 'info';
		await this.releaseCamera();

		try {
			const scannedUrl = new URL(decodedText.trim(), window.location.origin);
			const scanRoute = scannedUrl.pathname.match(/^\/scan\/([^/]+)\/?$/);
			if (!scanRoute) {
				throw new Error('QR tidak valid untuk proses check-in.');
			}

            window.location.assign(`${scanRoute[0]}`);
		} catch (error) {
			this.feedback = error.message || 'QR tidak valid.';
			this.feedbackType = 'error';
			this.isSubmitting = false;
		}
	},

	async stop() {
		await this.releaseCamera();
		this.feedback = '';
	},

	async releaseCamera() {
		const scanner = this.scanner;
		this.scanner = null;
		this.isScanning = false;

		if (!scanner) return;

		try {
			if (scanner.isScanning) await scanner.stop();
			scanner.clear();
		} catch {
			// Scanner may already have stopped after a successful decode.
		}
	},

	destroy() {
		this.releaseCamera();
	},
});

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();
