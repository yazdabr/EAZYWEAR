import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(intersect);
Alpine.plugin(collapse);

Alpine.data('quickView', () => ({
    open: false,

    title: '',
    series: '',
    image: '',
    price: '',

    // State untuk Drag to Dismiss
    startY: 0,
    offsetY: 0,
    isDragging: false,

    show(product) {

        this.title = product.title;
        this.series = product.series;
        this.image = product.image;
        this.price = product.price;

        this.offsetY = 0;
        this.open = true;

        // Kunci scroll background
        document.body.style.overflow = 'hidden';

    },

    close() {

        this.open = false;
        this.offsetY = 0;
        this.isDragging = false;

        // Buka kembali scroll
        document.body.style.overflow = '';

    },

    startDrag(e) {

        // Abaikan jika klik tombol
        if (e.target.closest('button')) return;

        this.isDragging = true;
        this.startY = e.touches
            ? e.touches[0].clientY
            : e.clientY;

    },

    onDrag(e) {

        if (!this.isDragging) return;

        if (e.cancelable) {
            e.preventDefault();
        }

        const current = e.touches
            ? e.touches[0].clientY
            : e.clientY;

        const delta = current - this.startY;

        // Hanya drag ke bawah
        if (delta > 0) {
            this.offsetY = delta;
        }

    },

    endDrag() {

        if (!this.isDragging) return;

        this.isDragging = false;

        if (this.offsetY > 100) {

            this.close();

        } else {

            this.offsetY = 0;

        }

    }

}));

Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    transitionTimeout: null,

    init() {
        this.dark = document.documentElement.classList.contains('dark');
    },

    toggle() {
        const root = document.documentElement;
        const next = !this.dark;
        const theme = next ? 'dark' : 'light';

        this.dark = next;

        root.classList.add('theme-transition');
        root.classList.toggle('dark', next);
        root.setAttribute('data-theme', theme);
        root.style.colorScheme = theme;

        try {
            window.localStorage.setItem('eazywear-theme', theme);
        } catch (error) {
            // Storage can be unavailable in private mode; the theme still applies for this page.
        }

        window.clearTimeout(this.transitionTimeout);
        this.transitionTimeout = window.setTimeout(() => {
            root.classList.remove('theme-transition');
        }, 420);
    },
});

Alpine.start();