export default function orderForm(initialData = {}) {
    return {
        items: [],
        status: initialData.status || "pendiente",
        deliveryType: initialData.deliveryType || "showroom",
        shippingCost: initialData.shippingCost || 0,
        freeShipping: initialData.freeShipping || false,
        clientMode: initialData.clientMode || "existing",
        clientId: initialData.clientId || null,
        clientSearch: initialData.clientSearch || "",
        clientResults: [],
        showClientResults: false,
        isSearchingClient: false,
        errors: initialData.errors || {},
        API: null,

        normalizeVariation(variation) {
            if (!variation) {
                return null;
            }

            return {
                id: String(variation.id),
                color: variation.product_color?.name || variation.color || "N/A",
                size: variation.size || "ÚNICO",
                stock: variation.stock ?? 0,
                effectivePrice: variation.effective_price ?? 0,
                originalPrice: variation.original_price ?? variation.effective_price ?? 0,
                hasActiveOffer: Boolean(variation.has_active_offer),
            };
        },

        normalizeInitialVariationOption(option) {
            if (!option) {
                return null;
            }

            return {
                id:
                    option.id !== null && option.id !== undefined
                        ? String(option.id)
                        : null,
                color: option.color || "N/A",
                size: option.size || "ÚNICO",
                stock: option.stock ?? null,
                effectivePrice: option.effective_price ?? 0,
                originalPrice:
                    option.original_price ?? option.effective_price ?? 0,
                hasActiveOffer: Boolean(option.has_active_offer),
                label: option.label || null,
                missing: Boolean(option.missing),
            };
        },

        mergeVariationOptions(initialOption, fetchedVariations) {
            const merged = [];
            const seen = new Set();
            const normalizedInitialOption =
                this.normalizeInitialVariationOption(initialOption);
            const normalizedFetchedVariations = (fetchedVariations || [])
                .map((variation) => this.normalizeVariation(variation))
                .filter(Boolean);

            if (
                normalizedInitialOption &&
                !normalizedInitialOption.missing &&
                normalizedInitialOption.id !== null
            ) {
                merged.push(normalizedInitialOption);
                seen.add(normalizedInitialOption.id);
            }

            normalizedFetchedVariations.forEach((variation) => {
                if (seen.has(variation.id)) {
                    return;
                }

                merged.push(variation);
                seen.add(variation.id);
            });

            return merged;
        },

        findMatchingInitialVariationOption(item) {
            const existingItems = initialData.existingItems || [];

            return (
                existingItems.find((existingItem) => {
                    if (
                        String(existingItem.product_id ?? "") !==
                        String(item.product_id ?? "")
                    ) {
                        return false;
                    }

                    if (item.variation_id && existingItem.variation_id) {
                        return (
                            String(existingItem.variation_id) ===
                            String(item.variation_id)
                        );
                    }

                    return Boolean(existingItem.initial_variation_option);
                })?.initial_variation_option ?? null
            );
        },

        getMatchingVariation(item, variationId = item?.variationId) {
            if (!item?.variations?.length || !variationId) {
                return null;
            }

            return (
                item.variations.find(
                    (variation) => String(variation.id) === String(variationId),
                ) ?? null
            );
        },

        syncVariationState(item, variationId = item?.variationId) {
            const normalizedVariationId = variationId ? String(variationId) : "";

            if (!normalizedVariationId) {
                item.variationId = "";
                item.selectedVariation = null;
                item.maxStock = null;
                return null;
            }

            const selectedOption = this.getMatchingVariation(
                item,
                normalizedVariationId,
            );

            if (!selectedOption) {
                item.selectedVariation = null;
                item.maxStock = null;
                return null;
            }

            item.variationId = String(selectedOption.id);
            item.selectedVariation = selectedOption;
            item.maxStock = selectedOption.stock ?? null;
            item.unitPrice = selectedOption.effectivePrice ?? 0;

            return selectedOption;
        },

        syncVariationSelect(select, item) {
            const desiredValue = item?.variationId ? String(item.variationId) : "";
            const optionSignature = (item?.variations || [])
                .map((variation) => String(variation.id))
                .join("|");
            const selectedOption = this.syncVariationState(item, desiredValue);

            void optionSignature;

            this.$nextTick(() => {
                if (!desiredValue || !selectedOption) {
                    if (select.value !== "") {
                        select.value = "";
                    }

                    return;
                }

                const hasMatchingOption = Array.from(select.options).some(
                    (option) => option.value === desiredValue,
                );
                if (!hasMatchingOption) {
                    return;
                }

                if (select.value !== desiredValue) {
                    select.value = "";
                    select.value = desiredValue;
                }
            });
        },

        init() {
            this.API = window.ORDER_ENDPOINTS;
            if (!this.API) {
                console.error("ORDER_ENDPOINTS no está definido");
                return;
            }

            const oldItems = initialData.oldItems;

            if (oldItems && Object.keys(oldItems).length > 0) {
                this.items = [];
                Object.values(oldItems).forEach((item) => {
                    const initialVariationOption =
                        this.normalizeInitialVariationOption(
                            this.findMatchingInitialVariationOption(item),
                        );

                    this.items.push({
                        productId: item.product_id || "",
                        productName: item.product_id ? "Cargando producto..." : "",
                        productSearch: item.product_id
                            ? "Cargando producto..."
                            : "",
                        variationId: item.variation_id
                            ? String(item.variation_id)
                            : "",
                        selectedVariation:
                            initialVariationOption &&
                            !initialVariationOption.missing
                                ? initialVariationOption
                                : null,
                        initialVariationOption,
                        quantity: item.quantity || 1,
                        unitPrice: item.unit_price || 0,
                        maxStock: null,
                        showResults: false,
                        isSearching: false,
                        hasSearched: false,
                        searchResults: [],
                        variations:
                            initialVariationOption &&
                            !initialVariationOption.missing
                                ? [initialVariationOption]
                                : [],
                    });

                    this.syncVariationState(this.items[this.items.length - 1]);
                    if (item.product_id) {
                        this.loadVariationsForItem(
                            this.items.length - 1,
                            item.product_id,
                        );
                    }
                });
            } else if (
                initialData.existingItems &&
                initialData.existingItems.length > 0
            ) {
                initialData.existingItems.forEach((item) => {
                    const initialVariationOption =
                        this.normalizeInitialVariationOption(
                            item.initial_variation_option,
                        );

                    this.items.push({
                        productId: item.product_id,
                        productName: item.product ? item.product.name : "Producto Eliminado",
                        productSearch: item.product
                            ? item.product.name
                            : "Producto Eliminado",
                        variationId: item.variation_id
                            ? String(item.variation_id)
                            : "",
                        selectedVariation:
                            initialVariationOption &&
                            !initialVariationOption.missing
                                ? initialVariationOption
                                : this.normalizeVariation(item.variation),
                        initialVariationOption,
                        quantity: item.quantity,
                        unitPrice: item.unit_price,
                        maxStock: null,
                        showResults: false,
                        isSearching: false,
                        hasSearched: false,
                        searchResults: [],
                        variations:
                            initialVariationOption &&
                            !initialVariationOption.missing
                                ? [initialVariationOption]
                                : [],
                    });

                    this.syncVariationState(this.items[this.items.length - 1]);
                    this.loadVariationsForItem(
                        this.items.length - 1,
                        item.product_id,
                    );
                });
            } else {
                this.addItem();
            }
        },

        addItem() {
            this.items.push({
                productId: "",
                productName: "",
                productSearch: "",
                variationId: "",
                selectedVariation: null,
                initialVariationOption: null,
                quantity: 1,
                unitPrice: 0,
                maxStock: null,
                showResults: false,
                isSearching: false,
                hasSearched: false,
                searchResults: [],
                variations: [],
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
                fetch(
                    `${this.API.searchProducts}?q=${encodeURIComponent(item.productSearch)}`,
                )
                    .then(async (res) => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
                    .then((data) => {
                        item.searchResults = data || [];
                        item.hasSearched = true;
                        item.showResults = true;
                    })
                    .catch((err) => {
                        console.error("Search error:", err);
                        item.searchResults = [];
                        item.hasSearched = true;
                    })
                    .finally(() => {
                        item.isSearching = false;
                    });
            }, 300);
        },

        handleProductInput(index) {
            let item = this.items[index];
            const currentSearch = item.productSearch;

            if (item.productId && currentSearch !== item.productName) {
                this.clearProduct(index, { preserveSearch: true });
                item.productSearch = currentSearch;
            }

            this.searchProduct(index);
        },

        selectProduct(index, product) {
            let item = this.items[index];
            item.productId = product.id;
            item.productName = product.name;
            item.showResults = false;
            item.productSearch = product.name;
            item.variationId = "";
            item.selectedVariation = null;
            item.initialVariationOption = null;
            item.unitPrice = 0;
            item.maxStock = null;
            item.variations = (product.variations || [])
                .map((variation) => this.normalizeVariation(variation))
                .filter(Boolean);

            this.clearError(`items.${index}.product_id`);
            this.clearError(`items.${index}.variation_id`);
            this.loadVariationsForItem(index, product.id);
        },

        loadVariationsForItem(index, productId) {
            if (!productId) {
                return;
            }

            fetch(`${this.API.searchProducts}?q=${encodeURIComponent(String(productId))}`)
                .then(async (res) => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then((products) => {
                    const product = products.find(
                        (p) => String(p.id) === String(productId),
                    );
                    if (product) {
                        let item = this.items[index];
                        if (!item || String(item.productId) !== String(productId)) {
                            return;
                        }

                        item.productName = product.name;
                        item.productSearch = product.name;
                        item.variations = this.mergeVariationOptions(
                            item.initialVariationOption,
                            product.variations || [],
                        );
                        this.syncVariationState(item);
                    }
                })
                .catch((err) =>
                    console.error("Error loading variations:", err),
                );
        },

        clearProduct(index, options = {}) {
            let item = this.items[index];
            const preserveSearch = options.preserveSearch === true;
            const currentSearch = item.productSearch;
            item.productId = "";
            item.productName = "";
            item.productSearch = preserveSearch ? currentSearch : "";
            item.variationId = "";
            item.selectedVariation = null;
            item.initialVariationOption = null;
            item.unitPrice = 0;
            item.variations = [];
            item.maxStock = null;

            this.clearError(`items.${index}.product_id`);
            this.clearError(`items.${index}.variation_id`);
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
                const response = await fetch(
                    `${this.API.searchClients}?q=${encodeURIComponent(this.clientSearch)}`,
                );
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
            this.clearError("client_id");
        },

        clearClient() {
            this.clientId = null;
            this.clientSearch = "";
            this.clientResults = [];
        },

        updatePrice(index) {
            let item = this.items[index];
            const variation = item.variations.find((v) => v.id == item.variationId);
            if (variation) {
                item.selectedVariation = variation;
                item.unitPrice = variation.effectivePrice;
                item.maxStock = variation.stock;
                if (item.quantity > variation.stock) item.quantity = variation.stock;

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
            return new Intl.NumberFormat("es-AR", {
                style: "currency",
                currency: "ARS",
            }).format(value);
        },

        calculateTotal() {
            return this.total;
        },

        get total() {
            let t = this.items.reduce((sum, item) => {
                return (
                    sum +
                    (parseFloat(item.quantity) || 0) *
                        (parseFloat(item.unitPrice) || 0)
                );
            }, 0);

            if (this.deliveryType === "shipping" && !this.freeShipping) {
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
        },
    };
}
