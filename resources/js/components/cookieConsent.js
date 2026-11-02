/**
 * Cookie banner. Two levels: "necessary" and "all".
 * Optional scripts are rendered on the server (components/cookie/scripts.blade.php), so the page reloads
 * when the choice starts or stops allowing them.
 */
export default ({ name, version, days, current, decided }) => ({
    open: !decided,
    current,

    choose(level) {
        const secure = location.protocol === "https:" ? "; Secure" : "";
        document.cookie = `${name}=${level}:${version}; max-age=${days * 86400}; path=/; SameSite=Lax${secure}`;
        this.open = false;

        const changesScripts = level === "all" || this.current === "all";
        this.current = level;
        if (changesScripts) window.location.reload();
    },
});
