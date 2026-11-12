<x-app-layout>


    @php
        $cart = session('cart', []);
        $cartItems = array_values($cart);

        $totalItems = 0;
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $totalItems += $item['quantity'];
            $subtotal += $item['price'] * $item['quantity'];
        }
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-300" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div
                class="grid grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-4 sm:gap-6"
                x-data="{
                    modalOpen: @js($errors->has('picture') || $errors->has('name') || $errors->has('price') || $errors->has('stock') || $errors->has('is_available')),
                    editing: @js(old('_method') === 'PUT'),
                    formAction: @js(old('_method') === 'PUT' ? route('products.update', old('product_id', 0)) : route('products.store')),
                    productId: @js(old('product_id')),
                    name: @js(old('name', '')),
                    sku: @js(old('sku', '')),
                    barcodeValue: '',
                    dataMatrixError: '',
                    productSearch: '',
                    productAvailability: 'all',
                    productItems: @js($products->map(fn ($product) => [
                        'name' => (string) $product->name,
                        'sku' => (string) $product->sku,
                        'isAvailable' => (bool) $product->is_available,
                    ])->values()),
                    price: @js(old('price', '')),
                    stock: @js(old('stock', '')),
                    isAvailable: @js((string) old('is_available', '1')),
                    previewUrl: null,
                    editImageUrl: null,
                    openCreate() {
                        this.editing = false;
                        this.formAction = @js(route('products.store'));
                        this.productId = '';
                        this.name = '';
                        this.sku = '';
                        this.barcodeValue = '';
                        this.dataMatrixError = '';
                        this.price = '';
                        this.stock = '';
                        this.isAvailable = '1';
                        this.previewUrl = null;
                        this.editImageUrl = null;
                        this.modalOpen = true;
                    },
                    openEdit(product) {
                        this.editing = true;
                        this.formAction = product.action;
                        this.productId = product.id;
                        this.name = product.name;
                        this.sku = product.sku;
                        this.barcodeValue = product.sku || `PID-${product.id}`;
                        this.dataMatrixError = '';
                        this.price = product.price;
                        this.stock = product.stock;
                        this.isAvailable = product.isAvailable ? '1' : '0';
                        this.previewUrl = null;
                        this.editImageUrl = product.imageUrl;
                        this.modalOpen = true;
                    },
                    async renderDataMatrix(canvas, value) {
                        this.dataMatrixError = '';

                        try {
                            if (this.modalOpen && this.editing && this.barcodeValue === value) {
                                await window.renderProductDataMatrix(canvas, value);
                            }
                        } catch (error) {
                            this.dataMatrixError = error instanceof Error
                                ? `Could not generate the Data Matrix code: ${error.message}`
                                : 'Could not generate the Data Matrix code.';
                        }
                    },
                    previewPicture(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.previewUrl = URL.createObjectURL(file);
                        }
                    },
                    matchesProduct(name, sku, isAvailable) {
                        const query = this.productSearch.trim().toLowerCase();
                        const matchesSearch = !query || name.toLowerCase().includes(query) || sku.toLowerCase().includes(query);
                        const matchesAvailability = this.productAvailability === 'all' || isAvailable;

                        return matchesSearch && matchesAvailability;
                    },
                    hasMatchingProducts() {
                        return this.productItems.some(product =>
                            this.matchesProduct(product.name, product.sku, product.isAvailable)
                        );
                    }
                }"
                @keydown.escape.window="modalOpen = false"
            >
                {{-- ===================== PRODUCTS ===================== --}}
                <section class="min-w-0 rounded-lg bg-white p-4 shadow-sm sm:p-6 dark:bg-gray-800" aria-labelledby="products-heading">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 id="products-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Products</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search and filter products</p>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <label for="product-search" class="sr-only">Search products by name or SKU</label>
                            <input
                                id="product-search"
                                type="search"
                                x-model="productSearch"
                                placeholder="Search name or SKU"
                                class="w-48 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                            <label for="product-availability" class="sr-only">Filter products by availability</label>
                            <select
                                id="product-availability"
                                x-model="productAvailability"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="all">All products</option>
                                <option value="available">Available only</option>
                            </select>
                            <button
                                type="button"
                                @click="openCreate()"
                                class="inline-flex shrink-0 items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 4a.75.75 0 0 1 .75.75v4.5h4.5a.75.75 0 0 1 0 1.5h-4.5v4.5a.75.75 0 0 1-1.5 0v-4.5h-4.5a.75.75 0 0 1 0-1.5h4.5v-4.5A.75.75 0 0 1 10 4Z" />
                                </svg>
                                Add Product
                            </button>
                        </div>
                    </div>

                    @if ($products->isEmpty())
                        <div class="flex min-h-64 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 px-4 text-center dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No products to display</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a product to get started.</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            @foreach ($products as $product)
                                <article
                                    x-show="matchesProduct($el.dataset.name, $el.dataset.sku, $el.dataset.available === '1')"
                                    data-name="{{ $product->name }}"
                                    data-sku="{{ $product->sku }}"
                                    data-available="{{ $product->is_available ? '1' : '0' }}"
                                    class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                                >
                                    <div class="flex gap-3 p-3">
                                        @if ($product->image_path)
                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}"
                                                alt="{{ $product->name }}"
                                                class="h-20 w-20 shrink-0 rounded-md object-cover"
                                            >
                                        @else
                                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500" aria-label="No product picture">
                                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4m9-4-9 4m0 0v10m-4.5-12 9 4" />
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h4>
                                                <span @class([
                                                    'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                                                    'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $product->is_available,
                                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => ! $product->is_available,
                                                ])>
                                                    {{ $product->is_available ? 'Available' : 'Unavailable' }}
                                                </span>
                                            </div>
                                            <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
                                            <div class="mt-2 flex items-center justify-between gap-2">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format((float) $product->price, 2) }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Stock: {{ $product->stock }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-1 border-t border-gray-200 px-3 py-2 dark:border-gray-700">
                                        {{-- Edit --}}
                                        <button
                                            type="button"
                                            aria-label="Edit {{ $product->name }}"
                                            title="Edit product"
                                            @click="openEdit({
                                                id: @js($product->id),
                                                action: @js(route('products.update', $product)),
                                                name: @js($product->name),
                                                sku: @js($product->sku),
                                                price: @js($product->price),
                                                stock: @js($product->stock),
                                                isAvailable: @js($product->is_available),
                                                imageUrl: @js($product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : null)
                                            })"
                                            class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-indigo-400"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="m13.586 3.586 2.828 2.828a2 2 0 0 1 0 2.828l-8.5 8.5a2 2 0 0 1-1.414.586H4a1 1 0 0 1-1-1v-2.5a2 2 0 0 1 .586-1.414l8.5-8.5a2 2 0 0 1 1.5-.828ZM12.172 5 5 12.172V15h2.828L15 7.828 12.172 5Z" />
                                            </svg>
                                        </button>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                aria-label="Delete {{ $product->name }}"
                                                title="Delete product"
                                                class="rounded-md p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-gray-400 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M8.5 2a1 1 0 0 0-.894.553L7.118 3.5H4a.75.75 0 0 0 0 1.5h.638l.698 10.466A2.75 2.75 0 0 0 8.08 18h3.84a2.75 2.75 0 0 0 2.744-2.534L15.362 5H16a.75.75 0 0 0 0-1.5h-3.118l-.488-.947A1 1 0 0 0 11.5 2h-3Zm-1.36 3 .69 10.366a1.25 1.25 0 0 0 1.248 1.134h1.844a1.25 1.25 0 0 0 1.248-1.134L12.86 5H7.14Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Add to cart --}}
                                        <form method="POST" action="{{ route('cart.add', $product) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                aria-label="Add {{ $product->name }} to cart"
                                                title="Add to cart"
                                                class="rounded-md p-2 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M1.5 2.75A.75.75 0 0 1 2.25 2h1.386a1.75 1.75 0 0 1 1.707 1.365L5.55 4.5h10.7a1.75 1.75 0 0 1 1.695 2.183l-1.2 4.8A1.75 1.75 0 0 1 15.05 12.8H7.1l.25 1.2h8.4a.75.75 0 0 1 0 1.5H6.74a.75.75 0 0 1-.735-.597L3.88 4.053a.25.25 0 0 0-.244-.195H2.25a.75.75 0 0 1-.75-.75ZM7.25 18a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Zm7 0a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <div
                            x-cloak
                            x-show="!hasMatchingProducts()"
                            style="display: none"
                            class="mt-4 rounded-lg border border-gray-200 px-4 py-8 text-center dark:border-gray-700"
                        >
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No products match your search or filter.</p>
                        </div>
                    @endif
                </section>

                {{-- ===================== CART ===================== --}}
                <section
                    x-data="cartState(@js($cartItems))"
                    class="min-w-0 rounded-lg bg-white p-4 shadow-sm sm:p-6 dark:bg-gray-800"
                    aria-labelledby="cart-heading"
                >
                    <div x-data="barcodeScanner" class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="cart-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cart</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span x-text="`${totalItems} ${totalItems === 1 ? 'item' : 'items'}`">{{ $totalItems }} {{ $totalItems === 1 ? 'item' : 'items' }}</span>
                                </p>
                            </div>

                            @if ($totalItems > 0)
                                <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    <span x-text="totalItems">{{ $totalItems }}</span>
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            <form x-ref="form" method="POST" action="{{ route('cart.scan') }}" class="flex gap-2">
                                @csrf
                                <label for="cart-barcode" class="sr-only">Scan or enter a product barcode</label>
                                <input
                                    x-ref="barcode"
                                    id="cart-barcode"
                                    name="barcode"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="Scan barcode or enter SKU"
                                    class="min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                <button
                                    type="submit"
                                    class="shrink-0 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    Add
                                </button>
                            </form>
                            <button
                                type="button"
                                x-show="!cameraOpen"
                                @click="startCamera()"
                                class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M6.5 3A1.5 1.5 0 0 0 5 4.5v.25H3.75A1.75 1.75 0 0 0 2 6.5v8.75C2 16.216 2.784 17 3.75 17h12.5A1.75 1.75 0 0 0 18 15.25V6.5a1.75 1.75 0 0 0-1.75-1.75H15V4.5A1.5 1.5 0 0 0 13.5 3h-7ZM10 7a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 1.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z" />
                                </svg>
                                Scan with camera
                            </button>
                            <button
                                type="button"
                                x-cloak
                                x-show="cameraOpen"
                                @click="stopCamera()"
                                class="text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100"
                            >
                                Stop camera
                            </button>
                            <video
                                x-ref="video"
                                x-cloak
                                x-show="cameraOpen"
                                autoplay
                                muted
                                playsinline
                                class="w-full rounded-md bg-black"
                            ></video>
                            <p
                                x-cloak
                                x-show="scanStatus"
                                x-text="scanStatus"
                                :role="scanState === 'error' ? 'alert' : 'status'"
                                aria-live="polite"
                                class="rounded-md px-3 py-2 text-sm"
                                :class="{
                                    'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300': scanState === 'starting' || scanState === 'scanning',
                                    'bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': scanState === 'not-found',
                                    'bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-300': scanState === 'detected',
                                    'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300': scanState === 'error',
                                    'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-300': scanState === 'idle'
                                }"
                            ></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Use a barcode scanner or device camera. Product barcodes encode the SKU.</p>
                        </div>
                    </div>

                    @if (count($cartItems) === 0)
                        <div class="flex min-h-48 items-center justify-center border-b border-gray-200 py-6 text-center dark:border-gray-700">
                            <div>
                                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6" />
                                    <circle cx="10" cy="20" r="1" />
                                    <circle cx="18" cy="20" r="1" />
                                </svg>
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Your cart is empty.</p>
                            </div>
                        </div>
                    @else
                        <div class="max-h-[400px] divide-y divide-gray-200 overflow-y-auto dark:divide-gray-700">
                            @foreach ($cartItems as $item)
                                <div class="flex gap-3 py-4">
                                    {{-- Product image --}}
                                    @if ($item['image'])
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['image']) }}"
                                            alt="{{ $item['name'] }}"
                                            class="h-16 w-16 shrink-0 rounded-md object-cover"
                                        >
                                    @else
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-md bg-gray-100 dark:bg-gray-700">
                                            <svg class="h-7 w-7 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4m9-4-9 4m0 0v10" />
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Product information --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex justify-between gap-2">
                                            <div class="min-w-0">
                                                <h4 class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</h4>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['sku'] }}</p>
                                                <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">₱{{ number_format($item['price'], 2) }}</p>
                                            </div>

                                            {{-- Remove --}}
                                            <form method="POST" action="{{ route('cart.remove', $item['id']) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                                    title="Remove"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6 2.75A.75.75 0 0 1 6.75 2h6.5a.75.75 0 0 1 .75.75V4h2.25a.75.75 0 0 1 0 1.5h-.6l-.7 10.5A2.2 2.2 0 0 1 12.76 18H7.24a2.2 2.2 0 0 1-2.19-2L4.35 5.5h-.6A.75.75 0 0 1 3.75 4H6V2.75Z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Quantity --}}
                                        <div class="mt-3 flex items-center justify-between">
                                            <div class="flex items-center rounded-md border border-gray-300 dark:border-gray-600">
                                                <form method="POST" action="{{ route('cart.decrease', $item['id']) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">−</button>
                                                </form>

                                                <span class="px-3 py-1 text-sm font-medium text-gray-800 dark:text-gray-200" x-text="items[{{ $item['id'] }}]?.quantity ?? {{ $item['quantity'] }}">{{ $item['quantity'] }}</span>

                                                <form
                                                    method="POST"
                                                    action="{{ route('cart.add', $item['id']) }}"
                                                    @submit.prevent="addItem($event, {{ $item['id'] }})"
                                                >
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        :disabled="processing[{{ $item['id'] }}]"
                                                        class="px-2 py-1 text-sm text-gray-600 hover:bg-gray-100 disabled:cursor-wait disabled:opacity-50 dark:text-gray-300 dark:hover:bg-gray-700"
                                                        aria-label="Increase {{ $item['name'] }} quantity"
                                                    >+</button>
                                                </form>
                                            </div>

                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                <span x-text="itemTotal({{ $item['id'] }})">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Totals + Payment --}}
                    <div class="space-y-3 pt-4">
                        <p
                            x-cloak
                            x-show="message && messageIsError"
                            x-text="message"
                            role="alert"
                            aria-live="polite"
                            class="rounded-md px-3 py-2 text-sm"
                            :class="messageIsError
                                ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                : 'bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-300'"
                        ></p>
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                            <span>Subtotal</span>
                            <span x-text="`₱${subtotal.toFixed(2)}`">₱{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between border-t border-gray-200 pt-3 font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <span>Total</span>
                            <span x-text="`₱${subtotal.toFixed(2)}`">₱{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('checkout.process') }}"
                            class="space-y-3"
                            x-data="{
                                total: {{ round($subtotal, 2) }},
                                method: @js(old('payment_method', 'cash')),
                                paid: @js((string) old('amount_paid', '')),
                                get change() {
                                    const diff = (parseFloat(this.paid) || 0) - this.total;
                                    return diff > 0 ? diff : 0;
                                },
                                get canPay() {
                                    return this.total > 0 && (parseFloat(this.paid) || 0) >= this.total;
                                },
                                init() {
                                    if (this.method !== 'cash') {
                                        this.paid = this.total.toFixed(2);
                                    }
                                    this.$watch('method', (value) => {
                                        this.paid = value !== 'cash' ? this.total.toFixed(2) : '';
                                    });
                                    this.$watch('total', (value) => {
                                        if (this.method !== 'cash') {
                                            this.paid = value.toFixed(2);
                                        }
                                    });
                                },
                                syncTotal(value) {
                                    this.total = value;
                                }
                            }"
                            @cart-updated.window="syncTotal($event.detail.total)"
                        >
                            @csrf

                            {{-- Payment method --}}
                            <div>
                                <label for="payment_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment method</label>
                                <select
                                    id="payment_method"
                                    name="payment_method"
                                    x-model="method"
                                    required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="gcash">GCash</option>
                                    <option value="maya">Maya</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                                @error('payment_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Amount paid --}}
                            <div>
                                <label for="amount_paid" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Amount paid</label>
                                <input
                                    id="amount_paid"
                                    name="amount_paid"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    x-model="paid"
                                    :readonly="method !== 'cash'"
                                    required
                                    placeholder="0.00"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    :class="method !== 'cash' ? 'bg-gray-50 dark:bg-gray-600' : ''"
                                >
                                @error('amount_paid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                <p
                                    x-show="paid !== '' && !canPay"
                                    style="display: none"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    Amount paid is less than the total.
                                </p>
                            </div>

                            {{-- Change --}}
                            <div class="flex justify-between rounded-md bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900 dark:bg-gray-700 dark:text-gray-100">
                                <span>Change</span>
                                <span x-text="'₱' + change.toFixed(2)">₱0.00</span>
                            </div>

                            <button
                                type="submit"
                                :disabled="!canPay"
                                class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-indigo-300 dark:focus:ring-offset-gray-800 dark:disabled:bg-indigo-900 dark:disabled:text-gray-400"
                            >
                                Checkout
                            </button>
                        </form>
                    </div>
                </section>

                {{-- ===================== PRODUCT MODAL ===================== --}}
                <div
                    x-show="modalOpen"
                    x-transition.opacity
                    style="display: none"
                    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 p-4"
                    @click.self="modalOpen = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="product-modal-title"
                >
                    <div class="my-auto w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800" @click.stop>
                        <form :action="formAction" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                            <input type="hidden" name="product_id" x-model="productId" :disabled="!editing">

                            <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                <div>
                                    <h3 id="product-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editing ? 'Edit Product' : 'Add Product'"></h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the product details below.</p>
                                </div>
                                <button type="button" @click="modalOpen = false" aria-label="Close dialog" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M6.22 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.78 3.72a.75.75 0 1 0 1.05 1.07L10 11.06l3.72 3.73a.75.75 0 0 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94 6.22 5.22Z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="max-h-[70vh] space-y-4 overflow-y-auto px-5 py-5">
                                <div>
                                    <label for="picture" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Product picture</label>
                                    <div class="flex items-center gap-4">
                                        <template x-if="previewUrl || editImageUrl">
                                            <img :src="previewUrl || editImageUrl" alt="Product preview" class="h-16 w-16 rounded-md object-cover">
                                        </template>
                                        <template x-if="!previewUrl && !editImageUrl">
                                            <div class="flex h-16 w-16 items-center justify-center rounded-md bg-gray-100 text-gray-400 dark:bg-gray-700">
                                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16.5V19h16v-2.5L16 12l-4 4-3-3-5 3.5ZM4 5h16v14H4V5Zm5 4h.01" />
                                                </svg>
                                            </div>
                                        </template>
                                        <input id="picture" name="picture" type="file" accept="image/*" @change="previewPicture($event)" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-900/40 dark:file:text-indigo-300">
                                    </div>
                                    @error('picture') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input id="name" name="name" type="text" x-model="name" required maxlength="255" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="sku" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">SKU</label>
                                    <input id="sku" name="sku" type="text" x-bind:value="editing ? sku : 'Generated automatically when saved'" readonly class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-500 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                </div>

                                <div
                                    x-cloak
                                    x-show="editing"
                                    x-effect="if (modalOpen && editing && barcodeValue) renderDataMatrix($refs.productDataMatrix, barcodeValue)"
                                    class="rounded-md border border-gray-200 p-4 text-center dark:border-gray-700"
                                >
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Product Data Matrix</p>
                                    <canvas
                                        x-ref="productDataMatrix"
                                        role="img"
                                        :aria-label="`Data Matrix code for ${name}`"
                                        class="mx-auto mt-3 max-w-full"
                                    ></canvas>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="barcodeValue"></p>
                                    <p
                                        x-show="dataMatrixError"
                                        x-text="dataMatrixError"
                                        role="alert"
                                        class="mt-2 text-sm text-red-600"
                                    ></p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                                        <input id="price" name="price" type="number" x-model="price" required min="0" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="stock" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock</label>
                                        <input id="stock" name="stock" type="number" x-model="stock" required min="0" step="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="is_available" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Availability</label>
                                    <select id="is_available" name="is_available" x-model="isAvailable" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                        <option value="1">Available</option>
                                        <option value="0">Unavailable</option>
                                    </select>
                                    @error('is_available') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                                <button type="button" @click="modalOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" x-text="editing ? 'Save Changes' : 'Add Product'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>