const registerRestaurantComponents = () => {
    Alpine.data('ratingInput', (initialValue = 0) => ({
        rating: Number(initialValue || 0),
        hover: 0,
        setRating(value) {
            this.rating = value;
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
    registerRestaurantComponents();
} else {
    document.addEventListener('alpine:init', registerRestaurantComponents);
}
