/** Live view for guests (toernooi or standalone spel). Stops refreshing once it is finished. */
export default (url, initial, claimBase) => ({
    s: initial,
    claimBase,
    error: false,
    handle: null,
    init() {
        if (this.finished) return;

        this.handle = setInterval(async () => {
            try {
                const res = await fetch(url, { headers: { Accept: "application/json" } });
                if (!res.ok) throw new Error("bad status");
                this.s = await res.json();
                this.error = false;
                if (this.finished) clearInterval(this.handle);
            } catch (e) {
                this.error = true;
            }
        }, 3000);
    },
    get finished() {
        return this.s.game.status === "finished";
    },
    get isTournament() {
        return this.s.kind === "tournament";
    },
    progress(player) {
        const target = this.s.game.target_score;
        return target ? Math.min(100, Math.max(0, (player.total / target) * 100)) : 0;
    },
});
