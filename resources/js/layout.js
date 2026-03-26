document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const storedTheme = window.localStorage.getItem('theme') || 'dark';

    const applyTheme = (mode) => {
        root.dataset.theme = mode;
    };

    window.setTheme = (mode) => {
        applyTheme(mode);
        window.localStorage.setItem('theme', mode);
    };

    applyTheme(storedTheme);
    document.documentElement.classList.add('js-ready');

    document.querySelectorAll('.js-reload-page').forEach((element) => {
        element.addEventListener('click', () => {
            window.location.reload();
        });
    });
});
