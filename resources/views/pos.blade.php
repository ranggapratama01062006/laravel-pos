@extends('layouts.app')

@section('content')
    <div class="pos-shell">
        <header class="pos-header">
            <div>
                <p class="brand">Kasir Laravel</p>
                <h1>Point of Sale Modern</h1>
                <p class="subtitle">Sistem kasir sederhana untuk penjualan, stok, dan struk cepat.</p>
            </div>
            <div class="top-badge top-user-panel">
                <div>
                    <span>Halo, {{ auth()->user()->name }}</span>
                    <strong>{{ auth()->user()->email }}</strong>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-button">Keluar</button>
                </form>
            </div>
        </header>

        <main class="pos-grid">
            <section class="card card-panel">
                <div class="card-head">
                    <h2>Produk & Pencarian</h2>
                    <span>{{ $products->count() }} Produk tersedia</span>
                </div>

                <div class="filters">
                    <input id="searchInput" type="search" placeholder="Cari nama produk atau SKU" />
                    <select id="categorySelect">
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="productList" class="product-list"></div>
            </section>

            <section class="card card-panel">
                <div class="card-head">
                    <h2>Keranjang</h2>
                    <button id="clearCart" class="btn-secondary">Kosongkan</button>
                </div>
                <div class="cart-body">
                    <div id="emptyCart" class="empty-state">Keranjang Anda masih kosong. Tambahkan produk untuk memulai transaksi.</div>
                    <table id="cartTable" class="cart-table hidden">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

            <section class="card card-summary">
                <div class="card-head">
                    <h2>Ringkasan Pembayaran</h2>
                </div>

                <div class="summary-row"><span>Subtotal</span><strong id="summarySubtotal">Rp0</strong></div>
                <div class="summary-row"><span>Pajak 10%</span><strong id="summaryTax">Rp0</strong></div>
                <div class="summary-row"><span>Diskon</span><strong id="summaryDiscount">Rp0</strong></div>
                <div class="summary-row total"><span>Total</span><strong id="summaryTotal">Rp0</strong></div>

                <div class="input-group">
                    <label for="discountInput">Diskon (Rp)</label>
                    <input id="discountInput" type="number" min="0" value="0" />
                </div>
                <div class="input-group">
                    <label for="cashInput">Tunai Diterima</label>
                    <input id="cashInput" type="number" min="0" value="0" />
                </div>
                <div class="summary-row"><span>Kembalian</span><strong id="summaryChange">Rp0</strong></div>

                <div class="input-group">
                    <label for="notesInput">Catatan</label>
                    <textarea id="notesInput" rows="3" placeholder="Contoh: Bayar pakai tunai atau pembayaran transfer"></textarea>
                </div>

                <div class="actions">
                    <button id="checkoutButton" class="btn-primary">Selesaikan Transaksi</button>
                    <button id="printButton" class="btn-secondary">Cetak Struk</button>
                </div>

                <div id="receiptPreview" class="receipt-card hidden"></div>
            </section>

            <section class="card card-panel card-admin">
                <div class="card-head">
                    <h2>Tambah Produk</h2>
                </div>
                <form id="productForm" class="product-form">
                    <div class="input-group">
                        <label for="productName">Nama Produk</label>
                        <input id="productName" name="name" type="text" required placeholder="Contoh: Kopi Latte" />
                    </div>
                    <div class="input-group">
                        <label for="productSku">SKU / Kode</label>
                        <input id="productSku" name="sku" type="text" required placeholder="Contoh: P001" />
                    </div>
                    <div class="input-group">
                        <label for="productCategory">Kategori</label>
                        <input id="productCategory" name="category" type="text" required placeholder="Contoh: Minuman" />
                    </div>
                    <div class="input-row">
                        <div class="input-group">
                            <label for="productPrice">Harga</label>
                            <input id="productPrice" name="price" type="number" min="0" required placeholder="25000" />
                        </div>
                        <div class="input-group">
                            <label for="productStock">Stok</label>
                            <input id="productStock" name="stock" type="number" min="0" required placeholder="20" />
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Tambahkan Produk</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        window.posData = {
            products: @json($products),
            categories: @json($categories),
            routes: {
                addProduct: '{{ url('/pos/products') }}',
                checkout: '{{ url('/pos/checkout') }}',
            },
        };
    </script>
    <script src="{{ asset('js/pos.js') }}"></script>
@endsection
