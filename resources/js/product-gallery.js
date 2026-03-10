export default (allImages, allVariations, initialColor) => ({
    allImages,
    allVariations,
    activeColor: initialColor,
    selectedVariation: null,
    currentSlide: 0,

    get colorNames() {
        return [...new Set(this.allVariations.map((v) => v.color))];
    },
    get activeImages() {
        if (
            this.allImages[this.activeColor] &&
            this.allImages[this.activeColor].length > 0
        ) {
            return this.allImages[this.activeColor];
        }
        const firstColor = Object.keys(this.allImages)[0];
        return firstColor ? this.allImages[firstColor] : [];
    },
    get activeVariations() {
        return this.allVariations.filter((v) => v.color === this.activeColor);
    },
    get selectedPrice() {
        if (!this.selectedVariation) return null;
        const v = this.allVariations.find(
            (v) => v.id == this.selectedVariation,
        );
        return v ? v.price : null;
    },
    get selectedStock() {
        if (!this.selectedVariation) return null;
        const v = this.allVariations.find(
            (v) => v.id == this.selectedVariation,
        );
        return v ? v.stock : null;
    },
    get minPrice() {
        if (this.activeVariations.length === 0) return 0;
        return Math.min(...this.activeVariations.map((v) => v.price));
    },

    selectColor(color) {
        this.activeColor = color;
        this.selectedVariation = null;
        this.currentSlide = 0;
    },
    formatPrice(price) {
        return (
            "$" +
            Number(price).toLocaleString("es-AR", { minimumFractionDigits: 0 })
        );
    },
});
