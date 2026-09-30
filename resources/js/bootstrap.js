import axios from 'axios';
import Alpine from 'alpinejs';

window.axios = axios;
window.Alpine = Alpine;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.heroCarousel = (heroes) => ({
    heroes: heroes || [],
    current: 0,
    autoPlay: null,

    init() {
        if (this.heroes.length <= 1) return;
        this.autoPlay = setInterval(() => {
            this.next();
        }, 6000);
    },

    next() {
        if (this.heroes.length === 0) return;
        this.current = (this.current + 1) % this.heroes.length;
        this.resetAutoPlay();
    },

    prev() {
        if (this.heroes.length === 0) return;
        this.current = (this.current - 1 + this.heroes.length) % this.heroes.length;
        this.resetAutoPlay();
    },

    goTo(index) {
        this.current = index;
        this.resetAutoPlay();
    },

    resetAutoPlay() {
        clearInterval(this.autoPlay);
        if (this.heroes.length > 1) {
            this.autoPlay = setInterval(() => {
                this.next();
            }, 6000);
        }
    },

    destroy() {
        clearInterval(this.autoPlay);
    },
});

Alpine.start();
