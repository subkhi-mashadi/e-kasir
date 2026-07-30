@extends('layouts.pos')
@section('title', 'Kasir')

@section('content')
<div class="flex flex-1 min-h-0 overflow-hidden"
     x-data="posApp()"
     x-init="init()">

    {{-- LEFT: Product Browser --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Search bar + sync status --}}
        <div class="bg-white border-b border-slate-100 px-4 py-3 shrink-0 flex items-center gap-2">
            <input type="text" x-model="search" placeholder="Cari produk..."
                   class="flex-1 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-amber-500 bg-slate-50">
            {{-- Online/Offline badge --}}
            <template x-if="!isOnline">
                <span class="shrink-0 text-xs font-semibold bg-orange-100 text-orange-600 px-2.5 py-1 rounded-lg flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 inline-block"></span>
                    Offline
                    <template x-if="pendingSyncCount > 0">
                        <span class="bg-orange-500 text-white text-xs px-1.5 py-0.5 rounded-full" x-text="pendingSyncCount"></span>
                    </template>
                </span>
            </template>
            <template x-if="isOnline && pendingSyncCount > 0">
                <span class="shrink-0 text-xs font-semibold bg-blue-50 text-blue-600 px-2.5 py-1 rounded-lg flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Sync <span x-text="pendingSyncCount"></span>
                </span>
            </template>
        </div>

        {{-- Product area --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div x-show="loading" class="flex items-center justify-center h-40 text-slate-400 text-sm">
                Memuat produk...
            </div>

            {{-- Search: flat grid --}}
            <template x-if="!loading && search.trim() !== ''">
                <div>
                    <div x-show="filteredProducts.length === 0" class="flex items-center justify-center h-40 text-slate-400 text-sm">
                        Produk tidak ditemukan.
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <button type="button" @click="openProduct(product)"
                                    :class="!product.is_available ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer'"
                                    :disabled="!product.is_available"
                                    class="bg-white rounded-2xl p-3 text-left transition-all shadow-sm border border-slate-100">
                                <div class="w-full aspect-square bg-amber-50 rounded-xl overflow-hidden flex items-center justify-center mb-2">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <span class="text-3xl" x-text="product.category_icon || '🍽️'"></span>
                                    </template>
                                </div>
                                <div class="text-xs font-semibold text-slate-800 leading-tight line-clamp-2 mb-1" x-text="product.name"></div>
                                <div class="text-xs text-amber-600 font-bold" x-text="'Rp ' + formatNumber(product.price)"></div>
                                <div class="mt-1">
                                    <template x-if="!product.is_available">
                                        <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">Tdk Tersedia</span>
                                    </template>
                                    <template x-if="product.is_available && product.track_stock && product.stock !== null && product.stock <= 0">
                                        <span class="text-xs text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">Tersedia</span>
                                    </template>
                                    <template x-if="product.is_available && product.track_stock && product.stock > 0">
                                        <span class="text-xs text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded-md" x-text="'Stok: ' + Math.floor(product.stock)"></span>
                                    </template>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- No search: grouped by category --}}
            <template x-if="!loading && search.trim() === ''">
                <div class="space-y-6">
                    <template x-for="group in productsByCategory" :key="group.category_id">
                        <div>
                            {{-- Category separator --}}
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-base leading-none" x-text="group.icon || '🍽️'"></span>
                                <span class="text-sm font-bold text-slate-700 uppercase tracking-wide" x-text="group.name"></span>
                                <div class="flex-1 h-px bg-slate-200"></div>
                                <span class="text-xs text-slate-400" x-text="group.products.length + ' item'"></span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                                <template x-for="product in group.products" :key="product.id">
                                    <button type="button" @click="openProduct(product)"
                                            :class="!product.is_available ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer'"
                                            :disabled="!product.is_available"
                                            class="bg-white rounded-2xl p-3 text-left transition-all shadow-sm border border-slate-100">
                                        <div class="w-full aspect-square bg-amber-50 rounded-xl overflow-hidden flex items-center justify-center mb-2">
                                            <template x-if="product.image_url">
                                                <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!product.image_url">
                                                <span class="text-3xl" x-text="product.category_icon || '🍽️'"></span>
                                            </template>
                                        </div>
                                        <div class="text-xs font-semibold text-slate-800 leading-tight line-clamp-2 mb-1" x-text="product.name"></div>
                                        <div class="text-xs text-amber-600 font-bold" x-text="'Rp ' + formatNumber(product.price)"></div>
                                        <div class="mt-1">
                                            <template x-if="!product.is_available">
                                                <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">Tdk Tersedia</span>
                                            </template>
                                            <template x-if="product.is_available && product.track_stock && product.stock !== null && product.stock <= 0">
                                                <span class="text-xs text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">Tersedia</span>
                                            </template>
                                            <template x-if="product.is_available && product.track_stock && product.stock > 0">
                                                <span class="text-xs text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded-md" x-text="'Stok: ' + Math.floor(product.stock)"></span>
                                            </template>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div x-show="productsByCategory.length === 0" class="flex items-center justify-center h-40 text-slate-400 text-sm">
                        Belum ada produk tersedia.
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="w-80 xl:w-96 flex flex-col bg-white border-l border-slate-100 shrink-0 overflow-hidden">

        {{-- Order type & table --}}
        <div class="px-4 py-3 border-b border-slate-100 space-y-2 shrink-0">
            <div class="flex gap-2">
                <button @click="orderType = 'dine_in'"
                        :class="orderType === 'dine_in' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'"
                        class="flex-1 py-1.5 rounded-xl text-xs font-medium transition-colors">
                    🍽️ Makan di Sini
                </button>
                <button @click="orderType = 'takeaway'"
                        :class="orderType === 'takeaway' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'"
                        class="flex-1 py-1.5 rounded-xl text-xs font-medium transition-colors">
                    📦 Bawa Pulang
                </button>
            </div>

            {{-- Nama pelanggan --}}
            <input type="text" x-model="customerName" placeholder="Nama pelanggan (opsional)"
                   class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-amber-500 bg-white">

            {{-- No meja (dine in) --}}
            <div x-show="orderType === 'dine_in'">
                <select x-model="tableId" class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-amber-500 bg-white">
                    <option value="">— Pilih No. Meja —</option>
                    @foreach ($tables as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-32 text-slate-300">
                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-sm">Keranjang kosong</span>
            </div>

            <template x-for="(item, index) in cart" :key="item.cartId">
                <div class="bg-slate-50 rounded-xl p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-slate-800 truncate" x-text="item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></div>
                            <template x-if="item.modifiers && item.modifiers.length > 0">
                                <div class="text-xs text-slate-400 mt-0.5" x-text="item.modifiers.map(m => m.option_name).join(', ')"></div>
                            </template>
                            <template x-if="item.notes">
                                <div class="text-xs text-slate-400 mt-0.5 italic" x-text="'Catatan: ' + item.notes"></div>
                            </template>
                        </div>
                        <button @click="removeItem(item.cartId)" class="text-red-400 hover:text-red-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-2">
                            <button @click="updateQty(item.cartId, -1)"
                                    class="w-6 h-6 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">−</button>
                            <span class="text-sm font-semibold text-slate-700 w-6 text-center" x-text="item.qty"></span>
                            <button @click="updateQty(item.cartId, 1)"
                                    class="w-6 h-6 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">+</button>
                        </div>
                        <span class="text-xs font-bold text-amber-600" x-text="'Rp ' + formatNumber(item.subtotal)"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totals & Checkout --}}
        <div class="border-t border-slate-100 px-4 py-3 space-y-2 shrink-0">
            {{-- Notes --}}
            <input type="text" x-model="notes" placeholder="Catatan order (opsional)"
                   class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-amber-500">

            {{-- Summary --}}
            <div class="space-y-1 text-xs text-slate-500">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span x-text="'Rp ' + formatNumber(subtotal)"></span>
                </div>
                <div x-show="taxTotal > 0" class="flex justify-between">
                    <span>Pajak</span>
                    <span x-text="'Rp ' + formatNumber(taxTotal)"></span>
                </div>
                <div x-show="discountAmount > 0" class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span x-text="'− Rp ' + formatNumber(discountAmount)"></span>
                </div>
            </div>

            <div class="flex justify-between items-center py-2 border-t border-slate-100">
                <span class="font-bold text-slate-800 text-sm">Total</span>
                <span class="font-bold text-amber-600 text-lg" x-text="'Rp ' + formatNumber(total)"></span>
            </div>

            <button @click="openPayment()"
                    :disabled="cart.length === 0"
                    :class="cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                    class="w-full py-3 rounded-2xl font-bold text-sm transition-colors">
                Bayar
            </button>
        </div>
    </div>

    {{-- Product Detail Modal (variant + modifier picker) --}}
    <div x-show="showProductModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col max-h-[90svh]" @click.stop>

            {{-- Header sticky --}}
            <div class="flex items-center justify-between p-5 border-b border-slate-100 shrink-0">
                <div>
                    <h3 class="font-bold text-slate-800" x-text="selectedProduct?.name"></h3>
                    <p class="text-sm text-amber-600 font-semibold" x-text="'Rp ' + formatNumber(itemTotalPrice())"></p>
                </div>
                <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4 min-h-0">
                {{-- Variants --}}
                <template x-if="selectedProduct?.variants?.length > 0">
                    <div>
                        <p class="text-xs font-semibold text-slate-700 mb-2">Varian</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="v in selectedProduct.variants" :key="v.id">
                                <button type="button" @click="selectedVariant = v"
                                        :class="selectedVariant?.id === v.id ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                        class="border rounded-xl px-3 py-1.5 text-xs font-medium transition-colors">
                                    <span x-text="v.name + (v.price_adjustment !== 0 ? ' (' + (v.price_adjustment > 0 ? '+' : '') + 'Rp ' + formatNumber(v.price_adjustment) + ')' : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Modifier Groups --}}
                <template x-for="group in (selectedProduct?.modifier_groups || [])" :key="group.id">
                    <div>
                        <p class="text-xs font-semibold text-slate-700 mb-2">
                            <span x-text="group.name"></span>
                            <span x-show="group.is_required" class="text-red-500 ml-1">*</span>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="opt in group.options" :key="opt.id">
                                <button type="button" @click="toggleModifier(group, opt)"
                                        :class="isModifierSelected(group.id, opt.id) ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                        class="border rounded-xl px-3 py-1.5 text-xs font-medium transition-colors">
                                    <span x-text="opt.name + (opt.price > 0 ? ' +Rp ' + formatNumber(opt.price) : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Qty --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Jumlah</span>
                    <div class="flex items-center gap-3">
                        <button @click="itemQty = Math.max(1, itemQty - 1)"
                                class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-slate-700 flex items-center justify-center">−</button>
                        <span class="text-base font-bold text-slate-800 w-8 text-center" x-text="itemQty"></span>
                        <button @click="itemQty++"
                                class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-slate-700 flex items-center justify-center">+</button>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Catatan Item</label>
                    <input type="text" x-model="itemNotes" placeholder="Opsional (mis: pedas extra)"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>
            </div>

            {{-- Footer sticky --}}
            <div class="shrink-0 p-5 border-t border-slate-100">
                <button @click="confirmAddToCart()"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-2xl text-sm transition-colors">
                    Tambah ke Keranjang · <span x-text="'Rp ' + formatNumber(itemTotalPrice() * itemQty)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPayment" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl flex flex-col max-h-[90svh]" @click.stop>
            <div class="flex items-center justify-between p-5 border-b border-slate-100 shrink-0">
                <h3 class="font-bold text-slate-800">Pembayaran</h3>
                <button @click="showPayment = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto min-h-0 p-5 space-y-4">
                {{-- Diskon --}}
                <div>
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Diskon (Rp)</label>
                    <input type="number" x-model="discountAmount" min="0" placeholder="0"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>

                {{-- Total --}}
                <div class="bg-amber-50 rounded-2xl p-4 text-center">
                    <template x-if="discountAmount > 0">
                        <p class="text-xs text-emerald-600 mb-0.5" x-text="'Diskon − Rp ' + formatNumber(discountAmount)"></p>
                    </template>
                    <p class="text-xs text-amber-700 mb-1">Total Tagihan</p>
                    <p class="text-2xl font-black text-amber-600" x-text="'Rp ' + formatNumber(total)"></p>
                </div>

                {{-- Payment Method --}}
                <div>
                    <p class="text-xs font-semibold text-slate-700 mb-2">Metode Pembayaran</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['cash' => '💵 Tunai', 'qris' => '📱 QRIS', 'transfer' => '🏦 Transfer', 'card' => '💳 Kartu', 'credit' => '📒 Kredit'] as $val => $label)
                        <button type="button" @click="paymentMethod = '{{ $val }}'"
                                :class="paymentMethod === '{{ $val }}' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                class="border rounded-xl py-2 text-xs font-medium transition-colors text-center">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Reference (non-cash) --}}
                <div x-show="paymentMethod !== 'cash'">
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Nomor Referensi</label>
                    <input type="text" x-model="paymentRef" placeholder="Opsional"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>

                {{-- Paid amount --}}
                <div x-show="paymentMethod === 'cash'">
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Uang Diterima</label>
                    <input type="number" x-model="paidAmount" min="0" placeholder="0"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">

                    {{-- Quick amounts --}}
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <template x-for="amount in quickAmounts()" :key="amount">
                            <button type="button" @click="paidAmount = amount"
                                    class="bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-600 text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors"
                                    x-text="'Rp ' + formatNumber(amount)">
                            </button>
                        </template>
                    </div>

                    {{-- Change --}}
                    <div x-show="paidAmount >= total && total > 0"
                         class="mt-3 bg-emerald-50 rounded-xl p-3 flex justify-between items-center">
                        <span class="text-xs text-emerald-700 font-medium">Kembalian</span>
                        <span class="text-base font-black text-emerald-600" x-text="'Rp ' + formatNumber(change)"></span>
                    </div>
                </div>

            </div>

            <div class="shrink-0 p-5 border-t border-slate-100">
                <button @click="checkout()"
                        :disabled="processing || (paymentMethod === 'cash' && paidAmount < total)"
                        :class="(processing || (paymentMethod === 'cash' && paidAmount < total))
                            ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                            : 'bg-amber-500 hover:bg-amber-600 text-white'"
                        class="w-full py-3 rounded-2xl font-bold text-sm transition-colors">
                    <span x-text="processing ? 'Memproses...' : 'Konfirmasi Pembayaran'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Incoming QR Orders Panel --}}
    <div x-show="showIncoming" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-end p-4 pt-16 bg-black/40"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showIncoming = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-y-auto max-h-[80vh]"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 sticky top-0 bg-white">
                <h3 class="font-bold text-slate-800">🔔 Pesanan Masuk
                    <template x-if="incomingOrders.length > 0">
                        <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full" x-text="incomingOrders.length"></span>
                    </template>
                </h3>
                <div class="flex items-center gap-3">
                    {{-- Volume control --}}
                    <div class="flex items-center gap-1.5" title="Volume notifikasi">
                        <button @click="notifVolume = notifVolume > 0 ? 0 : 0.7"
                                :title="notifVolume === 0 ? 'Suara mati' : 'Suara aktif'"
                                class="text-slate-400 hover:text-slate-600">
                            <svg x-show="notifVolume === 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                            </svg>
                            <svg x-show="notifVolume > 0" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m0 0l-4-4m4 4l4-4M9.172 9.172a4 4 0 000 5.656"/>
                            </svg>
                        </button>
                        <input type="range" min="0" max="1" step="0.1" x-model.number="notifVolume"
                               class="w-20 h-1.5 accent-amber-500 cursor-pointer">
                    </div>
                    <button @click="showIncoming = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="incomingOrders.length === 0" class="flex items-center justify-center h-32 text-slate-400 text-sm">
                Tidak ada pesanan QR masuk saat ini.
            </div>

            <div class="divide-y divide-slate-100">
                <template x-for="order in incomingOrders" :key="order.id">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-slate-800 text-sm" x-text="order.table_name"></p>
                                <p class="text-xs text-slate-400" x-text="(order.customer_name ? order.customer_name + ' · ' : '') + order.created_at"></p>
                            </div>
                            <p class="font-black text-amber-600 text-sm" x-text="'Rp ' + formatNumber(order.total)"></p>
                        </div>
                        <div class="space-y-1 mb-3">
                            <template x-for="item in order.items" :key="item.product_name + item.qty">
                                <div class="text-xs text-slate-600">
                                    <span class="font-semibold" x-text="item.qty + '× ' + item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></span>
                                    <template x-if="item.modifiers">
                                        <span class="text-slate-400" x-text="' (' + item.modifiers + ')'"></span>
                                    </template>
                                    <template x-if="item.notes">
                                        <span class="text-slate-400 italic" x-text="' — ' + item.notes"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <template x-if="order.notes">
                            <p class="text-xs text-slate-400 italic mb-2" x-text="'Catatan: ' + order.notes"></p>
                        </template>
                        {{-- Payment method badge --}}
                        <template x-if="order.preferred_payment">
                            <p class="text-xs mb-2">
                                <span :class="order.preferred_payment === 'qris' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'"
                                      class="px-2 py-0.5 rounded-full font-semibold"
                                      x-text="order.preferred_payment === 'qris' ? '📱 QRIS Statis' : '💵 Tunai'">
                                </span>
                            </p>
                        </template>
                        {{-- Bukti transfer (QRIS statis) --}}
                        <template x-if="order.payment_proof_url">
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1 font-semibold">Bukti Transfer:</p>
                                <a :href="order.payment_proof_url" target="_blank">
                                    <img :src="order.payment_proof_url" alt="Bukti"
                                         class="w-full max-h-40 object-contain rounded-xl border border-slate-200 hover:opacity-80 transition-opacity cursor-zoom-in bg-slate-50">
                                </a>
                            </div>
                        </template>
                        <template x-if="order.payment_proof_url">
                            <div class="flex gap-2">
                                <button @click="approveQrisProof(order.id)"
                                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold py-2 rounded-xl transition-colors">
                                    ✅ Setujui
                                </button>
                                <button @click="openRejectConfirm(order.id)"
                                        class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2 rounded-xl transition-colors">
                                    ✗ Tolak
                                </button>
                            </div>
                        </template>
                        <template x-if="!order.payment_proof_url">
                            <div class="flex gap-2">
                                <button @click="openPaymentConfirm(order)"
                                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold py-2 rounded-xl transition-colors">
                                    ✓ Konfirmasi Bayar
                                </button>
                                <button @click="openRejectConfirm(order.id)"
                                        class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2 rounded-xl transition-colors">
                                    ✗ Tolak
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Payment Confirmation Modal (for incoming QR orders) --}}
    <div x-show="showPaymentConfirm" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showPaymentConfirm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.stop>
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Konfirmasi Pembayaran</h3>
                <button @click="showPaymentConfirm = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4" x-show="confirmOrder">
                {{-- Order summary --}}
                <div class="bg-slate-50 rounded-xl p-3 text-xs space-y-1">
                    <div class="flex justify-between font-semibold text-slate-700">
                        <span x-text="confirmOrder?.table_name"></span>
                        <span x-text="confirmOrder?.customer_name"></span>
                    </div>
                    <template x-for="item in (confirmOrder?.items || [])" :key="item.product_name">
                        <div class="text-slate-500" x-text="item.qty + '× ' + item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></div>
                    </template>
                    <div class="flex justify-between font-black text-amber-600 pt-1 border-t border-slate-200">
                        <span>Total</span>
                        <span x-text="'Rp ' + formatNumber(confirmOrder?.total)"></span>
                    </div>
                </div>

                {{-- Payment method --}}
                <div>
                    <p class="text-xs font-semibold text-slate-600 mb-2">Metode Pembayaran</p>
                    <div class="flex gap-2">
                        <button type="button" @click="confirmPayMethod = 'cash'"
                                :class="confirmPayMethod === 'cash' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                class="flex-1 border-2 rounded-xl py-2 text-xs font-semibold transition-colors">
                            💵 Tunai
                        </button>
                        <button type="button" @click="confirmPayMethod = 'qris'"
                                :class="confirmPayMethod === 'qris' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                class="flex-1 border-2 rounded-xl py-2 text-xs font-semibold transition-colors">
                            📱 QRIS
                        </button>
                        <button type="button" @click="confirmPayMethod = 'transfer'"
                                :class="confirmPayMethod === 'transfer' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600'"
                                class="flex-1 border-2 rounded-xl py-2 text-xs font-semibold transition-colors">
                            🏦 Transfer
                        </button>
                    </div>
                </div>

                {{-- Cash: paid amount --}}
                <div x-show="confirmPayMethod === 'cash'">
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">Uang Diterima</label>
                    <input type="number" x-model="confirmPaidAmount" min="0" placeholder="0"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                    <div x-show="confirmPaidAmount >= (confirmOrder?.total || 0) && (confirmOrder?.total || 0) > 0"
                         class="mt-2 bg-emerald-50 rounded-xl p-2 flex justify-between text-xs">
                        <span class="text-emerald-700 font-medium">Kembalian</span>
                        <span class="font-black text-emerald-600" x-text="'Rp ' + formatNumber(Math.max(0, confirmPaidAmount - (confirmOrder?.total || 0)))"></span>
                    </div>
                </div>

                {{-- Non-cash: reference --}}
                <div x-show="confirmPayMethod !== 'cash'">
                    <label class="text-xs font-semibold text-slate-700 mb-1 block">No. Referensi (opsional)</label>
                    <input type="text" x-model="confirmRef" placeholder="ID transaksi / approval code"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
                </div>

                <button @click="confirmPayment()"
                        :disabled="confirmProcessing || (confirmPayMethod === 'cash' && parseFloat(confirmPaidAmount || 0) < (confirmOrder?.total || 0))"
                        :class="(confirmProcessing || (confirmPayMethod === 'cash' && parseFloat(confirmPaidAmount || 0) < (confirmOrder?.total || 0)))
                            ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                            : 'bg-emerald-500 hover:bg-emerald-600 text-white'"
                        class="w-full py-3 rounded-2xl font-bold text-sm transition-colors">
                    <span x-text="confirmProcessing ? 'Memproses...' : 'Konfirmasi Pembayaran Diterima'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Reject Confirm Modal --}}
    <div x-show="showRejectConfirm" x-cloak
         class="fixed inset-0 z-70 flex items-center justify-center p-4 bg-black/60"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showRejectConfirm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs" @click.stop>
            <div class="p-6 text-center">
                <div class="text-4xl mb-3">⚠️</div>
                <h3 class="font-black text-slate-800 text-base mb-1">Tolak Pesanan?</h3>
                <p class="text-sm text-slate-500 mb-5">Pesanan akan dibatalkan dan tidak bisa dikembalikan.</p>
                <div class="flex gap-3">
                    <button @click="showRejectConfirm = false"
                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 rounded-2xl text-sm transition-colors">
                        Batal
                    </button>
                    <button @click="confirmReject()"
                            :disabled="rejectProcessing"
                            :class="rejectProcessing ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-red-500 hover:bg-red-600 text-white'"
                            class="flex-1 font-bold py-2.5 rounded-2xl text-sm transition-colors">
                        <span x-text="rejectProcessing ? 'Menolak...' : 'Ya, Tolak'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction Success Modal --}}
    <div x-show="showSuccessModal" x-cloak
         class="fixed inset-0 z-80 flex items-center justify-center bg-black/60 p-4"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xs p-7 text-center">
            {{-- Online success --}}
            <template x-if="successOrderId">
                <div>
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 mb-1">Transaksi Berhasil</h2>
                    <p class="text-sm text-slate-500 mb-1" x-show="successInvoice">
                        Invoice: <span class="font-mono font-semibold text-slate-700" x-text="successInvoice"></span>
                    </p>
                    <p class="text-xs text-slate-400 mb-6">Pembayaran telah dikonfirmasi.</p>
                    <div class="flex gap-3">
                        <button @click="printReceipt(successOrderId)"
                                :disabled="printing"
                                class="flex-1 border-2 border-amber-400 text-amber-600 hover:bg-amber-50 disabled:opacity-60 font-semibold py-2.5 rounded-2xl text-sm transition-colors flex items-center justify-center gap-2">
                            <svg x-show="printing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span x-text="printing ? 'Mencetak...' : '🖨️ Cetak Struk'"></span>
                        </button>
                        <button @click="showSuccessModal = false; successOrderId = null; successInvoice = null"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-2xl text-sm transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
            {{-- Offline queued --}}
            <template x-if="!successOrderId">
                <div>
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 mb-1">Tersimpan</h2>
                    <p class="text-sm text-orange-600 font-medium mb-1">Mode Offline</p>
                    <p class="text-xs text-slate-400 mb-6">Order akan dikirim ke server saat koneksi pulih.</p>
                    <button @click="showSuccessModal = false"
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-2xl text-sm transition-colors">
                        OK
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Ready Orders Toast --}}
    <div x-show="readyOrders.length > 0" x-cloak
         class="fixed bottom-4 right-4 z-[70] w-80 space-y-2 pointer-events-none">
        <template x-for="order in readyOrders" :key="order.id">
            <div class="bg-emerald-600 text-white rounded-2xl shadow-2xl px-4 py-3 flex items-center gap-3 pointer-events-auto"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <span class="text-2xl shrink-0">🛎️</span>
                <div class="flex-1 min-w-0">
                    <p class="font-black text-sm leading-tight">Siap Diantarkan!</p>
                    <p class="text-emerald-100 text-xs truncate" x-text="order.table_name + (order.customer_name ? ' · ' + order.customer_name : '')"></p>
                </div>
                <button @click="markDelivered(order.id)"
                        class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-2.5 py-1.5 rounded-xl shrink-0 transition-colors">
                    Diantar ✓
                </button>
            </div>
        </template>
    </div>

</div>

<script>
function posApp() {
    return {
        // State
        products: [],
        categories: [],
        loading: true,
        search: '',

        // Incoming QR orders
        showIncoming: false,
        incomingOrders: [],
        _pollTimer: null,
        notifVolume: 0.7,

        // Ready orders (kitchen done)
        readyOrders: [],

        // Payment confirm modal
        showPaymentConfirm: false,
        confirmOrder: null,
        confirmPayMethod: 'cash',
        confirmPaidAmount: 0,
        confirmRef: '',
        confirmProcessing: false,

        // Reject confirm modal
        showRejectConfirm: false,
        rejectTargetId: null,
        rejectProcessing: false,

        // Cart
        cart: [],
        orderType: 'dine_in',
        tableId: '',
        customerId: '',
        customerName: '',
        notes: '',
        discountAmount: 0,

        // Product modal
        showProductModal: false,
        selectedProduct: null,
        selectedVariant: null,
        selectedModifiers: {},
        itemNotes: '',
        itemQty: 1,

        // Payment
        showPayment: false,
        paymentMethod: 'cash',
        paidAmount: 0,
        paymentRef: '',
        processing: false,

        // Success modal
        showSuccessModal: false,
        successInvoice: null,
        successOrderId: null,
        printing: false,

        // Offline / sync
        isOnline: navigator.onLine,
        pendingSyncCount: 0,

        // Computed
        get filteredProducts() {
            const q = this.search.toLowerCase();
            return this.products.filter(p => p.name.toLowerCase().includes(q));
        },

        get productsByCategory() {
            const map = new Map();
            for (const cat of this.categories) {
                map.set(cat.id, { category_id: cat.id, name: cat.name, icon: cat.icon || '', products: [] });
            }
            // uncategorised bucket
            map.set(null, { category_id: null, name: 'Lainnya', icon: '📦', products: [] });

            for (const p of this.products) {
                const key = p.category_id ?? null;
                if (map.has(key)) {
                    map.get(key).products.push(p);
                } else {
                    map.get(null).products.push(p);
                }
            }

            return [...map.values()].filter(g => g.products.length > 0);
        },

        get subtotal() {
            return this.cart.reduce((s, i) => s + i.subtotal, 0);
        },

        get taxTotal() {
            return this.cart.reduce((s, i) => s + i.taxAmount, 0);
        },

        get total() {
            return Math.max(0, this.subtotal + this.taxTotal - parseFloat(this.discountAmount || 0));
        },

        get change() {
            return Math.max(0, parseFloat(this.paidAmount || 0) - this.total);
        },

        // Methods
        init() {
            this.loadProducts();
            this.pollIncoming();
            this.pollReady();
            this.updatePendingCount();
            this._pollTimer = setInterval(() => { this.pollIncoming(); this.pollReady(); }, 15000);

            // Offline sync
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.updatePendingCount().then(n => {
                    if (n > 0 && window.__syncOutbox) window.__syncOutbox();
                });
            });
            window.addEventListener('offline', () => { this.isOnline = false; });
            window.addEventListener('order-synced', () => { this.updatePendingCount(); });

            // Button in top bar shows/hides based on POS page
            const btn = document.getElementById('btn-incoming');
            if (btn) btn.classList.remove('hidden');

            // Listen for top-bar button click
            document.addEventListener('open-incoming', () => { this.showIncoming = true; });

            this.$watch('incomingOrders', (orders) => {
                const badge = document.getElementById('incoming-badge');
                const btn   = document.getElementById('btn-incoming');
                if (!badge || !btn) return;
                if (orders.length > 0) {
                    badge.textContent = orders.length;
                    badge.classList.remove('hidden');
                    badge.style.display = 'flex';
                    btn.classList.add('text-amber-200');
                } else {
                    badge.classList.add('hidden');
                    badge.style.display = '';
                    btn.classList.remove('text-amber-200');
                    // Auto-close panel when no more orders
                    this.showIncoming = false;
                }
            });
        },

        async pollIncoming() {
            try {
                const res = await fetch('/pos/orders/incoming', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    const prev = this.incomingOrders.length;
                    this.incomingOrders = data.orders;
                    // Auto-open panel when new order arrives
                    if (data.orders.length > prev) {
                        this.showIncoming = true;
                        this.playNotifSound();
                    }
                }
            } catch (_) {}
        },

        openPaymentConfirm(order) {
            this.confirmOrder       = order;
            this.confirmPayMethod   = order.preferred_payment || 'cash';
            this.confirmPaidAmount  = order.total;
            this.confirmRef         = '';
            this.confirmProcessing  = false;
            this.showPaymentConfirm = true;
        },

        async confirmPayment() {
            if (!this.confirmOrder || this.confirmProcessing) return;
            if (this.confirmPayMethod === 'cash' && parseFloat(this.confirmPaidAmount || 0) < this.confirmOrder.total) return;

            this.confirmProcessing = true;
            const csrf = document.querySelector('meta[name=csrf-token]').content;

            try {
                const res = await fetch(`/pos/orders/${this.confirmOrder.id}/accept`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        payment_method: this.confirmPayMethod,
                        paid_amount:    parseFloat(this.confirmPaidAmount || this.confirmOrder.total),
                        reference:      this.confirmRef || null,
                    }),
                });
                if (res.ok) {
                    this.incomingOrders = this.incomingOrders.filter(o => o.id !== this.confirmOrder.id);
                    this.showPaymentConfirm = false;
                    this.confirmOrder = null;
                } else {
                    const d = await res.json();
                    alert(d.message || 'Gagal konfirmasi.');
                }
            } catch (_) {
                alert('Gagal terhubung ke server.');
            }
            this.confirmProcessing = false;
        },

        async pollReady() {
            try {
                const res = await fetch('/pos/orders/ready', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    this.readyOrders = data.orders;
                }
            } catch (_) {}
        },

        async updatePendingCount() {
            this.pendingSyncCount = window.__pendingCount ? await window.__pendingCount() : 0;
            return this.pendingSyncCount;
        },

        async markDelivered(orderId) {
            try {
                await fetch(`/kitchen/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ kitchen_status: 'delivered' }),
                });
                this.readyOrders = this.readyOrders.filter(o => o.id !== orderId);
            } catch (_) {}
        },

        playNotifSound() {
            if (this.notifVolume === 0) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const vol = ctx.createGain();
                vol.gain.value = this.notifVolume;
                vol.connect(ctx.destination);

                [[880, 0, 0.15], [1100, 0.18, 0.15], [880, 0.36, 0.2]].forEach(([freq, start, dur]) => {
                    const osc = ctx.createOscillator();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    osc.connect(vol);
                    osc.start(ctx.currentTime + start);
                    osc.stop(ctx.currentTime + start + dur);
                });
            } catch (_) {}
        },

        async approveQrisProof(orderId) {
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const res = await fetch(`/app/qris-verification/${orderId}/approve`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            });
            if (res.ok) {
                this.incomingOrders = this.incomingOrders.filter(o => o.id !== orderId);
            } else {
                alert('Gagal menyetujui. Coba lagi.');
            }
        },

        openRejectConfirm(orderId) {
            this.rejectTargetId   = orderId;
            this.rejectProcessing = false;
            this.showRejectConfirm = true;
        },

        async confirmReject() {
            if (!this.rejectTargetId || this.rejectProcessing) return;
            this.rejectProcessing = true;
            try {
                await fetch(`/pos/orders/${this.rejectTargetId}/reject`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                this.incomingOrders = this.incomingOrders.filter(o => o.id !== this.rejectTargetId);
                this.showRejectConfirm = false;
                this.rejectTargetId   = null;
            } catch (_) {}
            this.rejectProcessing = false;
        },

        async loadProducts() {
            this.loading = true;
            const db = window.__db;
            try {
                if (navigator.onLine) {
                    const res = await fetch('/pos/products');
                    const data = await res.json();
                    this.products   = data.products;
                    this.categories = data.categories;
                    // Cache to Dexie for offline use
                    if (db) {
                        await db.products.clear();
                        await db.categories.clear();
                        await db.products.bulkPut(data.products);
                        await db.categories.bulkPut(data.categories);
                    }
                } else {
                    // Fallback to Dexie cache
                    if (db) {
                        this.products   = await db.products.toArray();
                        this.categories = await db.categories.toArray();
                    }
                }
            } catch (e) {
                // Network error — try Dexie fallback
                if (db) {
                    try {
                        this.products   = await db.products.toArray();
                        this.categories = await db.categories.toArray();
                    } catch (_) {}
                }
            }
            this.loading = false;
        },

        openProduct(product) {
            if (!product.is_available) return;
            if (product.variants.length === 0 && product.modifier_groups.length === 0) {
                this.addToCart(product, null, {}, 1, '');
            } else {
                this.selectedProduct = product;
                this.selectedVariant = product.variants.length > 0 ? product.variants[0] : null;
                this.selectedModifiers = {};
                this.itemNotes = '';
                this.itemQty = 1;
                this.showProductModal = true;
            }
        },

        itemTotalPrice() {
            if (!this.selectedProduct) return 0;
            const base = this.selectedProduct.price + (this.selectedVariant?.price_adjustment || 0);
            const modTotal = Object.values(this.selectedModifiers).flat().reduce((s, m) => s + m.price, 0);
            return base + modTotal;
        },

        toggleModifier(group, option) {
            if (!this.selectedModifiers[group.id]) {
                this.selectedModifiers[group.id] = [];
            }
            const list = this.selectedModifiers[group.id];
            const idx = list.findIndex(m => m.id === option.id);
            if (idx >= 0) {
                list.splice(idx, 1);
            } else if (group.is_multiple) {
                list.push(option);
            } else {
                this.selectedModifiers[group.id] = [option];
            }
        },

        isModifierSelected(groupId, optionId) {
            return (this.selectedModifiers[groupId] || []).some(m => m.id === optionId);
        },

        confirmAddToCart() {
            if (!this.selectedProduct) return;
            this.addToCart(
                this.selectedProduct,
                this.selectedVariant,
                this.selectedModifiers,
                this.itemQty,
                this.itemNotes
            );
        },

        addToCart(product, variant, modifiers, qty, notes) {
            const modList = Object.values(modifiers).flat();
            const modPrice = modList.reduce((s, m) => s + m.price, 0);
            const unitPrice = product.price + (variant?.price_adjustment || 0) + modPrice;

            const modKey = modList.map(m => m.id).sort().join(',');
            const existing = this.cart.find(i =>
                i.product_id === product.id &&
                (i.product_variant_id || null) === (variant?.id || null) &&
                i.modifiers.map(m => m.id).sort().join(',') === modKey &&
                (i.notes || '') === (notes || '')
            );

            if (existing) {
                existing.qty += qty;
                existing.subtotal = existing.unit_price * existing.qty;
                existing.taxAmount = existing.subtotal * (existing.tax_rate / 100);
            } else {
                const subtotal = unitPrice * qty;
                this.cart.push({
                    cartId: Date.now() + Math.random(),
                    product_id: product.id,
                    product_variant_id: variant?.id || null,
                    product_name: product.name,
                    variant_name: variant?.name || null,
                    unit_price: unitPrice,
                    tax_rate: product.tax_rate,
                    qty: qty,
                    taxAmount: subtotal * (product.tax_rate / 100),
                    subtotal: subtotal,
                    notes: notes,
                    modifiers: modList,
                });
            }
            this.showProductModal = false;
        },

        removeItem(cartId) {
            this.cart = this.cart.filter(i => i.cartId !== cartId);
        },

        updateQty(cartId, delta) {
            const item = this.cart.find(i => i.cartId === cartId);
            if (!item) return;
            item.qty = Math.max(1, item.qty + delta);
            item.subtotal = item.unit_price * item.qty;
            item.taxAmount = item.subtotal * (item.tax_rate / 100);
        },

        openPayment() {
            if (this.cart.length === 0) return;
            this.paidAmount = this.total;
            this.showPayment = true;
        },

        quickAmounts() {
            const t = this.total;
            const base = [t, 50000, 100000, 200000];
            const rounded = [
                Math.ceil(t / 5000) * 5000,
                Math.ceil(t / 10000) * 10000,
                Math.ceil(t / 50000) * 50000,
            ];
            return [...new Set([...base, ...rounded])].filter(a => a >= t).sort((a, b) => a - b).slice(0, 5);
        },

        async checkout() {
            if (this.cart.length === 0 || this.processing) return;
            if (this.paymentMethod === 'cash' && parseFloat(this.paidAmount) < this.total) return;

            this.processing = true;

            const payload = {
                type: this.orderType,
                table_id: this.tableId || null,
                customer_id: this.customerId || null,
                customer_name: this.customerName || null,
                notes: this.notes || null,
                discount_amount: this.discountAmount || 0,
                paid_amount: this.paymentMethod === 'cash' ? parseFloat(this.paidAmount) : this.total,
                payment_method: this.paymentMethod,
                payment_reference: this.paymentRef || null,
                items: this.cart.map(item => ({
                    product_id: item.product_id,
                    product_variant_id: item.product_variant_id || null,
                    qty: item.qty,
                    notes: item.notes || null,
                    modifiers: item.modifiers.map(m => ({ modifier_option_id: m.id })),
                })),
            };

            // Offline: queue to Dexie outbox
            if (!navigator.onLine && window.__queueOrder) {
                await window.__queueOrder(payload);
                await this.updatePendingCount();
                this.successOrderId  = null;
                this.successInvoice  = null;
                this.showPayment     = false;
                this.showSuccessModal = true;
                this._resetCart();
                this.processing = false;
                return;
            }

            try {
                const res = await fetch('/pos/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (res.ok) {
                    this.successOrderId  = data.order_id;
                    this.successInvoice  = data.invoice_no;
                    this.showPayment     = false;
                    this.showSuccessModal = true;
                    this._resetCart();
                } else {
                    const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Terjadi kesalahan.');
                    alert(msg);
                }
            } catch (e) {
                // Network error — fall back to offline queue
                if (window.__queueOrder) {
                    await window.__queueOrder(payload);
                    await this.updatePendingCount();
                    this.successOrderId  = null;
                    this.successInvoice  = null;
                    this.showPayment     = false;
                    this.showSuccessModal = true;
                    this._resetCart();
                } else {
                    alert('Gagal memproses pembayaran. Periksa koneksi internet.');
                }
            }

            this.processing = false;
        },

        _resetCart() {
            this.cart           = [];
            this.tableId        = '';
            this.customerId     = '';
            this.customerName   = '';
            this.notes          = '';
            this.discountAmount = 0;
            this.paidAmount     = 0;
            this.paymentRef     = '';
        },

        printReceipt(orderId) {
            this.printing = true;
            const iframe = document.createElement('iframe');
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.cssText = 'display:none;';
            iframe.src = `/pos/orders/${orderId}/receipt`;
            document.body.appendChild(iframe);
            iframe.addEventListener('load', () => {
                iframe.contentWindow.print();
                const done = () => {
                    iframe.remove();
                    this.printing = false;
                    window.removeEventListener('focus', done);
                };
                window.addEventListener('focus', done, { once: true });
            });
        },

        formatNumber(n) {
            return Number(n || 0).toLocaleString('id-ID');
        },
    };
}
</script>
@endsection
