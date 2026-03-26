const registerPlaceComponents = () => {
    Alpine.data('impressionInput', (initialValue = 0) => ({
        impression: Number(initialValue || 0),
        hover: 0,
        setImpression(value) {
            this.impression = value;
        },
    }));

    Alpine.data('deleteModal', () => ({
        open: false,
        show() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        hide() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
    }));
};

if (window.Alpine) {
    registerPlaceComponents();
} else {
    document.addEventListener('alpine:init', registerPlaceComponents);
}
