export default () => ({
    init() {
        // No initialization needed for pure editorial scroll
    },

    scrollCategories(direction) {
        const container = this.$refs.container;
        
        // Scroll 80% of the visible width for a confident, editorial "page" feel
        // This avoids hard pagination while moving enough content
        const scrollAmount = container.clientWidth * 0.80;
        
        const delta = direction === 'left' ? -scrollAmount : scrollAmount;

        container.scrollBy({
            left: delta,
            behavior: 'smooth'
        });
    }
});
