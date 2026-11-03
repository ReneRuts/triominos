/**
 * Preview of the points for a stone. Mirrors App\Scoring\ScoreCalculator.
 * The server always recalculates; this only gives feedback while typing.
 */
const BRIDGE = 40;
const HEX = { 1: 50, 2: 60, 3: 70 };
const TRIPLE_BONUS = 10;
const START_000_TOTAL = 40;
const MAX_DIGIT = 5;
const NEXT = { a: "b", b: "c", c: null };
const PREVIOUS = { a: null, b: "a", c: "b" };

export default (cfg) => ({
    mode: cfg.mode,
    firstStone: cfg.firstStone, // nothing laid yet in this spel
    tripleDone: cfg.tripleDone, // a stone with three equal digits was already laid in this spel
    a: "",
    b: "",
    c: "",
    bridge: false,
    hexagons: 0,
    manual: "",

    /** One digit from 0 to 5 per box; anything else is dropped. A valid digit moves on to the next box. */
    setDigit(key, event) {
        const digit = event.target.value
            .replace(new RegExp(`[^0-${MAX_DIGIT}]`, "g"), "")
            .slice(-1);
        event.target.value = digit;
        this[key] = digit;

        const next = NEXT[key];
        if (digit !== "" && next) this.$refs[next].focus();
    },
    /** Backspace in an empty box goes back to the previous one. */
    back(key, event) {
        const previous = PREVIOUS[key];
        if (event.target.value === "" && previous) {
            event.preventDefault();
            this.$refs[previous].focus();
        }
    },
    valid() {
        return [this.a, this.b, this.c].every(
            (v) =>
                v !== "" && Number.isInteger(+v) && +v >= 0 && +v <= MAX_DIGIT,
        );
    },
    z(n) {
        return +n === 0 ? 1 : +n;
    },
    value() {
        if (!this.valid()) return 0;
        return this.mode === "multiply"
            ? this.z(this.a) * this.z(this.b) * this.z(this.c)
            : +this.a + +this.b + +this.c;
    },
    lines() {
        if (!this.valid()) return [];
        const value = this.value();
        const out = [
            { label: `Steen ${this.a}-${this.b}-${this.c}`, points: value },
        ];
        if (+this.a === +this.b && +this.b === +this.c && !this.tripleDone) {
            out.push({
                label: "Eerste steen met drie dezelfde cijfers van het spel",
                points: TRIPLE_BONUS,
            });
            if (+this.a === 0 && this.firstStone) {
                out.push({
                    label: "Start met 0-0-0: steen telt 40",
                    points: START_000_TOTAL - value - TRIPLE_BONUS,
                });
            }
        }
        if (this.bridge) out.push({ label: "Brug gesloten", points: BRIDGE });
        if (+this.hexagons > 0) {
            out.push({
                label:
                    +this.hexagons === 1
                        ? "Zeshoek"
                        : `${this.hexagons} zeshoeken`,
                points: HEX[+this.hexagons],
            });
        }
        return out;
    },
    total() {
        if (this.manual !== "" && this.manual !== null) return +this.manual;
        return this.lines().reduce((s, l) => s + l.points, 0);
    },
});
