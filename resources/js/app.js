import './bootstrap';
import Alpine from 'alpinejs';

// ── Theme Store ──────────────────────────────────────────────────────────────
// Mirrors React ThemeContext: localStorage → system preference → default dark
Alpine.store('theme', {
    value: localStorage.getItem('theme') ||
           (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'),

    init() {
        this.apply();
    },

    toggle() {
        this.value = this.value === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.value);
        this.apply();
    },

    apply() {
        document.documentElement.classList.remove('light', 'dark');
        document.documentElement.classList.add(this.value);

        if (this.value === 'dark') {
            document.body.classList.add('dark');
        } else {
            document.body.classList.remove('dark');
        }
    },

    get isDark() {
        return this.value === 'dark';
    },
});

// ── Toast Store ──────────────────────────────────────────────────────────────
// Mirrors React sonner toast utility with success/error/info types
Alpine.store('toast', {
    items: [],
    _nextId: 0,

    show(message, type = 'info', duration = 3000) {
        const id = ++this._nextId;
        this.items.push({ id, message, type, visible: true });

        setTimeout(() => {
            this.dismiss(id);
        }, duration);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    },

    info(message) {
        this.show(message, 'info');
    },

    dismiss(id) {
        const index = this.items.findIndex(item => item.id === id);
        if (index !== -1) {
            this.items[index].visible = false;
            setTimeout(() => {
                this.items = this.items.filter(item => item.id !== id);
            }, 300);
        }
    },
});

window.Alpine = Alpine;
Alpine.start();
