export default function orderForm(initialData = {}) {
    return {
        items: [],
        deliveryType: initialData.deliveryType || 'showroom',
        shippingCost: initialData.shippingCost || 0,
        freeShipping: initialData.freeShipping || false,
        clientMode: initialData.clientMode || 'existing',
        errors: initialData.errors || {},

        init() {
            const oldItems = initialData.oldItems;
            
            // Prefer old input if available (after validation error)
            if (oldItems && Object.keys(oldItems).length > 0) {
                this.items = [];
                Object.values(oldItems).forEach(item => {
                    this.items.push({
                        productId: item.product_id || '',
                        productName: 'Producto Previo (Seleccione para refrescar)',
                        productSearch: '',
                        variationId: item.variation_id || '',
                        quantity: item.quantity || 1,
                        unitPrice: item.unit_price || 0,
                        maxStock: null,
                        showResults: false,
                        isSearching: false,
                        hasSearched: false,
                        searchResults: [],
                        variations: []
                    });
                    if (item.product_id) {
                        this.loadVariationsForItem(this.items.length - 1, item.product_id);
                    }
                });
            } else if (initialData.existingItems && initialData.existingItems.length > 0) {
                // Fallback to DB items (for Edit mode)
                initialData.existingItems.forEach(item => {
                    this.items.push({
                        productId: item.product_id,
                        productName: item.product ? item.product.name : 'Producto Eliminado',
                        productSearch: '',
                        variationId: item.variation_id,
                        quantity: item.quantity,
                        unitPrice: item.unit_price,
                        maxStock: null,
                        showResults: false,
                        isSearching: false,
                        hasSearched: false,
                        searchResults: [],
                        variations: []
                    });
                    this.loadVariationsForItem(this.items.length - 1, item.product_id);
                });
            } else {
                this.addItem();
            }
        },

        addItem() {
            this.items.push({
                productId: '',
                productName: '',
                productSearch: '',
                variationId: '',
                quantity: 1,
                unitPrice: 0,
                maxStock: null,
                showResults: false,
                isSearching: false,
                hasSearched: false,
                searchResults: [],
                variations: []
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
            if (this.items.length === 0) this.addItem();
        },

        searchProduct(index) {
            let item = this.items[index];
            if (item.productSearch.length < 2) {
                item.searchResults = [];
                item.hasSearched = false;
                return;
            }

            item.isSearching = true;
            fetch(`/admin/products/search?q=${encodeURIComponent(item.productSearch)}`)
                .then(res => res.json())
                .then(data => {
                    item.searchResults = data;
                    item.hasSearched = true;
                })
                .finally(() => {
                    item.isSearching = false;
                });
        },

        selectProduct(index, product) {
            let item = this.items[index];
            item.productId = product.id;
            item.productName = product.name;
            item.showResults = false;
            item.productSearch = '';
            item.variations = product.variations || [];
            
            this.clearError(`items.${index}.product_id`);
            this.loadVariationsForItem(index, product.id);
        },

        loadVariationsForItem(index, productId) {
            fetch(`/admin/products/search?q=${productId}`)
                .then(res => res.json())
                .then(products => {
                    const product = products.find(p => p.id == productId);
                    if (product) {
                        let item = this.items[index];
                        item.productName = product.name;
                        item.variations = product.variations || [];
                        
                        // If we have a variation selected (from old/existing), update maxStock
                        if (item.variationId) {
                            const v = item.variations.find(v => v.id == item.variationId);
                            if (v) item.maxStock = v.stock;
                        }
                    }
                })
                .catch(err => console.error("Error loading variations:", err));
        },

        clearProduct(index) {
            let item = this.items[index];
            item.productId = '';
            item.productName = '';
            item.variationId = '';
            item.unitPrice = 0;
            item.variations = [];
            item.maxStock = null;

            this.clearError(`items.${index}.product_id`);
            this.clearError(`items.${index}.variation_id`);
            this.clearError(`items.${index}.unit_price`);
            this.clearError(`items.${index}.quantity`);
        },

        updatePrice(index) {
            let item = this.items[index];
            const variation = item.variations.find(v => v.id == item.variationId);
            if (variation) {
                item.unitPrice = variation.price;
                item.maxStock = variation.stock;
                if (item.quantity > variation.stock) item.quantity = variation.stock;
                
                this.clearError(`items.${index}.unit_price`);
                this.clearError(`items.${index}.variation_id`);
            }
        },

        validateQuantity(index) {
            let item = this.items[index];
            if (item.maxStock !== null && item.quantity > item.maxStock) {
                item.quantity = item.maxStock;
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS'
            }).format(value);
        },

        get total() {
            let t = this.items.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unitPrice) || 0);
            }, 0);
            
            if (this.deliveryType === 'shipping' && !this.freeShipping) {
                t += parseFloat(this.shippingCost) || 0;
            }
            return t;
        },

        getError(key) {
            return this.errors[key] ? this.errors[key][0] : null;
        },

        clearError(key) {
            if (this.errors && this.errors[key]) {
                delete this.errors[key];
            }
        }
    };
}
