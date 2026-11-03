/** Live view for guests (toernooi or standalone spel). Stops refreshing once it is finished. */
const INTERVAL = 4000;
const TIMEOUT = 8000;
const MAX_FAILURES = 3; // the warning only shows after this many failed requests in a row

export default (url, initial, claimBase) => ({
    s: initial,
    claimBase,
    error: false,
    failures: 0,
    busy: false,
    timer: null,
    init() {
        if (this.finished) return;

        this.schedule();
        document.addEventListener("visibilitychange", () => {
            if (!document.hidden && !this.finished && !this.busy) {
                clearTimeout(this.timer);
                this.poll();
            }
        });
    },
    schedule() {
        this.timer = setTimeout(() => this.poll(), INTERVAL);
    },
    async poll() {
        if (this.busy) return;
        if (document.hidden) return this.schedule();

        this.busy = true;
        const controller = new AbortController();
        const abort = setTimeout(() => controller.abort(), TIMEOUT);

        try {
            const res = await fetch(url, {
                headers: { Accept: "application/json" },
                cache: "no-store",
                signal: controller.signal,
            });
            if (!res.ok) throw new Error("bad status");
            this.s = await res.json();
            this.failures = 0;
            this.error = false;
        } catch (e) {
            this.failures++;
            this.error = this.failures >= MAX_FAILURES;
        } finally {
            clearTimeout(abort);
            this.busy = false;
        }

        if (!this.finished) this.schedule();
    },
    get finished() {
        return this.s.game.status === "finished";
    },
    get isTournament() {
        return this.s.kind === "tournament";
    },
    progress(player) {
        const target = this.s.game.target_score;
        return target
            ? Math.min(100, Math.max(0, (player.total / target) * 100))
            : 0;
    },
});
