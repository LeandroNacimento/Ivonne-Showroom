export default function orderForm(initialData = {}) {
    return {
        items: [],
        deliveryType: initialData.deliveryType || 'showroom',
        shippingCost: initialData.shippingCost || 0,
        freeShipping: initialData.freeShipping || false,
        clientMode: initialData.clientMode || 'existing',
        clientId: initialData.clientId || null,
        clientSearch: initialData.clientSearch || '',
        clientResults: [],
        showClientResults: false,
        isSearchingClient: false,
        errors: initialData.errors || {},
        API: null,

        init() {
            this.API = window.ORDER_ENDPOINTS;
            if (!this.API) {
                console.error('ORDER_ENDPOINTS no está definido');
                return;
            }

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
                item.showResults = false;
                return;
            }

            clearTimeout(this._searchTimeout);

            this._searchTimeout = setTimeout(() => {
                item.isSearching = true;
                fetch(`${this.API.searchProducts}?q=${encodeURIComponent(item.productSearch)}`)
                    .then(async res => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        item.searchResults = data || [];
                        item.hasSearched = true;
                        item.showResults = true;
                    })
                    .catch(err => {
                        console.error("Search error:", err);
                        item.searchResults = [];
                        item.hasSearched = true;
                    })
                    .finally(() => {
                        item.isSearching = false;
                    });
            }, 300);
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
            fetch(`${this.API.searchProducts}?q=${productId}`)
                .then(async res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then(products => {
                    const product = products.find(p => String(p.id) === String(productId));
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

        async searchClient() {
            if (!this.clientSearch.trim()) {
                this.clientResults = [];
                this.showClientResults = false;
                return;
            }

            this.isSearchingClient = true;

            try {
                const response = await fetch(`${this.API.searchClients}?q=${encodeURIComponent(this.clientSearch)}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();

                this.clientResults = data || [];
                this.showClientResults = true;
            } catch (e) {
                console.error("Client search error:", e);
                this.clientResults = [];
            } finally {
                this.isSearchingClient = false;
            }
        },

        selectClient(client) {
            this.clientId = client.id;
            this.clientSearch = client.name;
            this.showClientResults = false;
            this.clearError('client_id');
        },

        clearClient() {
            this.clientId = null;
            this.clientSearch = '';
            this.clientResults = [];
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
