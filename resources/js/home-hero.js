export default ({ total = 0, interval = 5000 } = {}) => ({
    active: 0,
    total,
    interval,
    timer: null,

    init() {
        if (this.total < 2) {
            return;
        }

        this.resume();
    },

    next() {
        if (this.total < 2) {
            return;
        }

        this.active = (this.active + 1) % this.total;
    },

    prev() {
        if (this.total < 2) {
            return;
        }

        this.active = (this.active - 1 + this.total) % this.total;
    },

    goTo(index) {
        if (this.total < 2) {
            return;
        }

        this.active = index;
        this.resume();
    },

    pause() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    },

    resume() {
        this.pause();

        if (this.total < 2) {
            return;
        }

        this.timer = setInterval(() => {
            this.next();
        }, this.interval);
    },
});
