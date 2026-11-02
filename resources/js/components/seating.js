/** Seating order around the table (clockwise), set once before the first spel. */
export default (players) => ({
    list: players,
    up(i) {
        if (i > 0) [this.list[i - 1], this.list[i]] = [this.list[i], this.list[i - 1]];
    },
    down(i) {
        if (i < this.list.length - 1) [this.list[i + 1], this.list[i]] = [this.list[i], this.list[i + 1]];
    },
});
