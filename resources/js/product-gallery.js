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

    currentImageIndex: 0,

    selectColor(color) {
        this.activeColor = color;
        this.selectedVariation = null;
        this.currentSlide = 0;
        this.currentImageIndex = 0;
        if (this.$refs && this.$refs.galleryScroll) {
            this.$refs.galleryScroll.scrollTo({ left: 0, behavior: 'instant' });
        }
    },

    next() {
        if (this.currentImageIndex < this.activeImages.length - 1) {
            this.goTo(this.currentImageIndex + 1);
        }
    },

    prev() {
        if (this.currentImageIndex > 0) {
            this.goTo(this.currentImageIndex - 1);
        }
    },

    goTo(index) {
        this.currentImageIndex = index;
        if (this.$refs && this.$refs.galleryScroll) {
            const container = this.$refs.galleryScroll;
            container.scrollTo({
                left: container.clientWidth * index,
                behavior: 'smooth'
            });
        }
    },

    updateIndexFromScroll(event) {
        const container = event.target;
        this.currentImageIndex = Math.round(container.scrollLeft / container.clientWidth);
    },

    formatPrice(price) {
        return (
            "$" +
            Number(price).toLocaleString("es-AR", { minimumFractionDigits: 0 })
        );
    },
});
