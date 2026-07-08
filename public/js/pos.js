const elements = {
    productList: document.getElementById('productList'),
    searchInput: document.getElementById('searchInput'),
    categorySelect: document.getElementById('categorySelect'),
    cartTable: document.getElementById('cartTable'),
    cartTableBody: document.querySelector('#cartTable tbody'),
    emptyCart: document.getElementById('emptyCart'),
    clearCart: document.getElementById('clearCart'),
    summarySubtotal: document.getElementById('summarySubtotal'),
    summaryTax: document.getElementById('summaryTax'),
    summaryDiscount: document.getElementById('summaryDiscount'),
    summaryTotal: document.getElementById('summaryTotal'),
    summaryChange: document.getElementById('summaryChange'),
    discountInput: document.getElementById('discountInput'),
    cashInput: document.getElementById('cashInput'),
    notesInput: document.getElementById('notesInput'),
    checkoutButton: document.getElementById('checkoutButton'),
    printButton: document.getElementById('printButton'),
    receiptPreview: document.getElementById('receiptPreview'),
    productForm: document.getElementById('productForm'),
};

const state = {
    products: window.posData.products || [],
    cart: [],
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

const findProduct = (productId) => state.products.find((product) => product.id === productId);

const calculateCart = () => {
    const subtotal = state.cart.reduce((sum, item) => sum + item.subtotal, 0);
    const discount = Number(elements.discountInput.value || 0);
    const tax = subtotal * 0.1;
    const total = Math.max(0, subtotal + tax - discount);
    const cash = Number(elements.cashInput.value || 0);
    const change = Math.max(0, cash - total);

    elements.summarySubtotal.textContent = formatCurrency(subtotal);
    elements.summaryTax.textContent = formatCurrency(tax);
    elements.summaryDiscount.textContent = formatCurrency(discount);
    elements.summaryTotal.textContent = formatCurrency(total);
    elements.summaryChange.textContent = formatCurrency(change);

    return { subtotal, tax, discount, total, cash, change };
};

const renderProducts = () => {
    const keyword = elements.searchInput.value.trim().toLowerCase();
    const category = elements.categorySelect.value;
    const filtered = state.products.filter((product) => {
        const matchesKeyword = product.name.toLowerCase().includes(keyword)
            || product.sku.toLowerCase().includes(keyword);
        const matchesCategory = category === 'all' || product.category === category;
        return matchesKeyword && matchesCategory;
    });

    if (!filtered.length) {
        elements.productList.innerHTML = '<div class="empty-state">Tidak ada produk yang sesuai filter.</div>';
        return;
    }

    elements.productList.innerHTML = filtered.map((product) => {
        return `
            <div class="product-card">
                <div class="product-details">
                    <div class="product-name">${product.name}</div>
                    <div class="product-meta">
                        <span>${product.sku}</span>
                        <span>${product.category}</span>
                        <span>Stok ${product.stock}</span>
                    </div>
                </div>
                <div class="product-actions">
                    <div class="product-price">${formatCurrency(product.price)}</div>
                    <button class="add-product" data-product-id="${product.id}">Tambah</button>
                </div>
            </div>
        `;
    }).join('');

    document.querySelectorAll('.add-product').forEach((button) => {
        button.addEventListener('click', () => {
            const productId = Number(button.dataset.productId);
            addToCart(productId);
        });
    });
};

const renderCart = () => {
    if (!state.cart.length) {
        elements.cartTable.classList.add('hidden');
        elements.emptyCart.classList.remove('hidden');
        elements.cartTableBody.innerHTML = '';
        calculateCart();
        return;
    }

    elements.cartTable.classList.remove('hidden');
    elements.emptyCart.classList.add('hidden');

    elements.cartTableBody.innerHTML = state.cart.map((item) => {
        return `
            <tr>
                <td>${item.product.name}</td>
                <td>
                    <div class="cart-qty">
                        <button class="qty-button" data-action="decrease" data-product-id="${item.product.id}">-</button>
                        ${item.quantity}
                        <button class="qty-button" data-action="increase" data-product-id="${item.product.id}">+</button>
                    </div>
                </td>
                <td>${formatCurrency(item.unit_price)}</td>
                <td>${formatCurrency(item.subtotal)}</td>
                <td>
                    <button class="qty-button" data-action="remove" data-product-id="${item.product.id}">×</button>
                </td>
            </tr>
        `;
    }).join('');

    document.querySelectorAll('[data-action]').forEach((button) => {
        const productId = Number(button.dataset.productId);
        const action = button.dataset.action;

        button.addEventListener('click', () => {
            if (action === 'increase') {
                updateQuantity(productId, 1);
            }
            if (action === 'decrease') {
                updateQuantity(productId, -1);
            }
            if (action === 'remove') {
                removeFromCart(productId);
            }
        });
    });

    calculateCart();
};

const addToCart = (productId) => {
    const product = findProduct(productId);
    if (!product) return;

    const existing = state.cart.find((item) => item.product.id === productId);
    const quantity = existing ? existing.quantity + 1 : 1;

    if (quantity > product.stock) {
        alert(`Stok ${product.name} tidak cukup.`);
        return;
    }

    if (existing) {
        existing.quantity = quantity;
        existing.subtotal = existing.quantity * existing.unit_price;
    } else {
        state.cart.push({
            product,
            quantity: 1,
            unit_price: Number(product.price),
            subtotal: Number(product.price),
        });
    }

    renderCart();
};

const updateQuantity = (productId, delta) => {
    const item = state.cart.find((cartItem) => cartItem.product.id === productId);
    if (!item) return;

    const nextQuantity = item.quantity + delta;
    if (nextQuantity < 1) {
        removeFromCart(productId);
        return;
    }
    if (nextQuantity > item.product.stock) {
        alert(`Stok ${item.product.name} tidak cukup.`);
        return;
    }

    item.quantity = nextQuantity;
    item.subtotal = item.quantity * item.unit_price;
    renderCart();
};

const removeFromCart = (productId) => {
    state.cart = state.cart.filter((item) => item.product.id !== productId);
    renderCart();
};

const clearCart = () => {
    state.cart = [];
    renderCart();
};

const sendCheckout = async () => {
    const { subtotal, tax, discount, total, cash, change } = calculateCart();

    if (!state.cart.length) {
        alert('Tambahkan produk terlebih dahulu.');
        return;
    }
    if (cash < total) {
        alert('Jumlah tunai belum mencukupi.');
        return;
    }

    const payload = {
        items: state.cart.map((item) => ({
            product_id: item.product.id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            subtotal: item.subtotal,
        })),
        cash_received: cash,
        discount,
        notes: elements.notesInput.value.trim(),
    };

    const response = await fetch(window.posData.routes.checkout, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(payload),
    });

    const data = await response.json();
    if (!response.ok) {
        const message = data.message || 'Terjadi kesalahan saat checkout.';
        alert(message);
        return;
    }

    renderReceipt(data.receipt);
    clearCart();
};

const renderReceipt = (receipt) => {
    const header = `Struk Transaksi\nNomor: #${receipt.order_id}\nTanggal: ${receipt.created_at}\n\n`;
    const itemsText = receipt.items
        .map((item) => `• ${item.quantity}× ${findProduct(item.product_id)?.name || 'Produk'}: ${formatCurrency(item.subtotal)}`)
        .join('\n');

    const footer = `\nSubtotal: ${formatCurrency(receipt.subtotal)}\nPajak: ${formatCurrency(receipt.tax)}\nDiskon: ${formatCurrency(receipt.discount)}\nTotal: ${formatCurrency(receipt.total)}\nTunai: ${formatCurrency(receipt.cash_received)}\nKembalian: ${formatCurrency(receipt.change_due)}\n\nCatatan: ${receipt.notes || '-'}\n\nTerima kasih sudah berbelanja!`;

    elements.receiptPreview.classList.remove('hidden');
    elements.receiptPreview.innerHTML = `
        <h3>Preview Struk</h3>
        <pre>${header}${itemsText}${footer}</pre>
    `;
};

const createProduct = async (formData) => {
    const response = await fetch(window.posData.routes.addProduct, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(formData),
    });

    const data = await response.json();

    if (!response.ok) {
        const errors = data.errors ? Object.values(data.errors).flat().join('\n') : data.message || 'Gagal menambahkan produk.';
        alert(errors);
        return;
    }

    const product = data.product;
    state.products.push(product);
    if (!state.products.some((item) => item.category === product.category)) {
        const option = document.createElement('option');
        option.value = product.category;
        option.textContent = product.category;
        elements.categorySelect.appendChild(option);
    }

    renderProducts();
    elements.productForm.reset();
    alert('Produk berhasil ditambahkan.');
};

const bindEvents = () => {
    elements.searchInput.addEventListener('input', renderProducts);
    elements.categorySelect.addEventListener('change', renderProducts);
    elements.clearCart.addEventListener('click', clearCart);
    elements.discountInput.addEventListener('input', calculateCart);
    elements.cashInput.addEventListener('input', calculateCart);
    elements.checkoutButton.addEventListener('click', sendCheckout);
    elements.printButton.addEventListener('click', () => window.print());

    elements.productForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = {
            name: document.getElementById('productName').value.trim(),
            sku: document.getElementById('productSku').value.trim(),
            category: document.getElementById('productCategory').value.trim(),
            price: Number(document.getElementById('productPrice').value),
            stock: Number(document.getElementById('productStock').value),
        };
        createProduct(formData);
    });
};

const initialize = () => {
    renderProducts();
    renderCart();
    bindEvents();
};

initialize();
