/**
 * Preview of the points for a stone. Mirrors App\Scoring\ScoreCalculator.
 * The server always recalculates; this only gives feedback while typing.
 */
const BRIDGE = 40;
const HEX = { 1: 50, 2: 60, 3: 70 };
const TRIPLE_BONUS = 10;
const START_000_TOTAL = 40;
const MAX_DIGIT = 5;

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

    valid() {
        return [this.a, this.b, this.c].every(
            (v) => v !== "" && Number.isInteger(+v) && +v >= 0 && +v <= MAX_DIGIT,
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
        const out = [{ label: `Steen ${this.a}-${this.b}-${this.c}`, points: value }];
        if (+this.a === +this.b && +this.b === +this.c && !this.tripleDone) {
            out.push({ label: "Eerste steen met drie dezelfde cijfers van het spel", points: TRIPLE_BONUS });
            if (+this.a === 0 && this.firstStone) {
                out.push({ label: "Start met 0-0-0: steen telt 40", points: START_000_TOTAL - value - TRIPLE_BONUS });
            }
        }
        if (this.bridge) out.push({ label: "Brug gesloten", points: BRIDGE });
        if (+this.hexagons > 0) {
            out.push({
                label: +this.hexagons === 1 ? "Zeshoek" : `${this.hexagons} zeshoeken`,
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
