/** Copies the live link. Falls back to execCommand where the clipboard API is unavailable (plain http, older browsers). */
export default () => ({
    copied: false,
    async copy() {
        const input = this.$refs.url;

        try {
            await navigator.clipboard.writeText(input.value);
        } catch (e) {
            input.focus();
            input.select();
            input.setSelectionRange(0, input.value.length);
            if (!document.execCommand("copy")) return;
        }

        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },
});
