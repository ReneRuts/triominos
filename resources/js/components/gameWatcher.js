/** Polls the server and reloads when somebody else changed the page, unless the user is typing. */
export default (url, version) => ({
    version,
    stale: false,
    init() {
        setInterval(async () => {
            try {
                const res = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin" });
                if (!res.ok) return;
                const data = await res.json();
                if (data.version === this.version) return;

                const el = document.activeElement;
                const typing = el && ["INPUT", "SELECT", "TEXTAREA"].includes(el.tagName) && el.value !== "";
                if (typing) this.stale = true;
                else window.location.reload();
            } catch (e) {
                /* offline, try again next tick */
            }
        }, 5000);
    },
});
