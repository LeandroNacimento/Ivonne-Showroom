export default (allImages, allVariations, initialColor, initialPricing) => ({
    allImages,
    allVariations,
    activeColor: initialColor,
    selectedVariation: null,
    currentSlide: 0,
    initialPricing,

    get colorNames() {
        return [...new Set(this.allVariations.map((variation) => variation.color))];
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
        return this.allVariations.filter(
            (variation) => variation.color === this.activeColor,
        );
    },

    get selectedVariationData() {
        if (!this.selectedVariation) {
            return null;
        }

        return (
            this.allVariations.find(
                (variation) => variation.id == this.selectedVariation,
            ) ?? null
        );
    },

    get displayPricing() {
        if (this.selectedVariationData) {
            return {
                price: this.selectedVariationData.effective_price,
                originalPrice: this.selectedVariationData.original_price,
                hasActiveOffer: this.selectedVariationData.has_active_offer,
            };
        }

        return this.initialPricing;
    },

    get selectedStock() {
        return this.selectedVariationData ? this.selectedVariationData.stock : null;
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
