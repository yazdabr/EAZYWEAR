import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

window.Alpine = Alpine;

Alpine.plugin(intersect);

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

        // KUNCI SCROLL LATAR BELAKANG
        document.body.style.overflow = 'hidden';
    },

    close() {
        this.open = false;
        this.offsetY = 0;
        this.isDragging = false;

        // BUKA KEMBALI SCROLL LATAR BELAKANG
        document.body.style.overflow = '';
    },

    startDrag(e) {
            // Jika yang diklik adalah tombol close atau elemen di dalamnya, abaikan drag
            if (e.target.closest('button')) return;

            this.isDragging = true;
            this.startY = e.touches ? e.touches[0].clientY : e.clientY;
        },

    onDrag(e) {
        if (!this.isDragging) return;

        // Mencegah scroll default browser saat drag aktif
        if (e.cancelable) {
            e.preventDefault();
        }

        const current = e.touches ? e.touches[0].clientY : e.clientY;
        const delta = current - this.startY;
        
        // Hanya izinkan drag ke arah bawah
        if (delta > 0) {
            this.offsetY = delta;
        }
    },

    endDrag() {
        if (!this.isDragging) return;
        this.isDragging = false;
        
        // Jika ditarik ke bawah lebih dari 100px, tutup modal
        if (this.offsetY > 100) {
            this.close();
        } else {
            // Balikkan ke posisi semula (snap back)
            this.offsetY = 0;
        }
    }
}));

Alpine.start();