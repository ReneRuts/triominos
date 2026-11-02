/** Countdown for the per-turn time limit. Nothing happens automatically: somebody confirms the timeout. */
export default (limit) => ({
    limit,
    left: limit,
    running: false,
    handle: null,
    start() {
        this.stop();
        this.left = this.limit;
        this.running = true;
        this.handle = setInterval(() => {
            if (this.left > 0) this.left--;
            if (this.left === 0) {
                clearInterval(this.handle);
                this.running = false;
            }
        }, 1000);
    },
    stop() {
        clearInterval(this.handle);
        this.running = false;
    },
    reset() {
        this.stop();
        this.left = this.limit;
    },
    get expired() {
        return this.left === 0;
    },
    get label() {
        const m = Math.floor(this.left / 60);
        const s = String(this.left % 60).padStart(2, "0");
        return `${m}:${s}`;
    },
});
