<script>
    window.RangRez = window.RangRez || {};
    window.RangRez.themes = ['splash', 'festival', 'ink'];
    window.RangRez.defaultTheme = 'splash';

    window.RangRez.applyTheme = function (theme) {
        const allowed = window.RangRez.themes;
        const next = allowed.includes(theme) ? theme : window.RangRez.defaultTheme;

        document.documentElement.dataset.theme = next;
        window.localStorage.setItem('rangrez.theme', next);

        window.dispatchEvent(new CustomEvent('rangrez-theme-changed', { detail: { theme: next } }));

        return next;
    };

    window.RangRez.currentTheme = function () {
        const stored = window.localStorage.getItem('rangrez.theme');

        return window.RangRez.themes.includes(stored) ? stored : window.RangRez.defaultTheme;
    };

    window.RangRez.applyTheme(window.RangRez.currentTheme());
</script>
