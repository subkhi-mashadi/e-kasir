<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name }} — Menu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<div x-data="qrMenu()" x-cloak>

    {{-- Top bar --}}
    <div class="bg-amber-500 text-white px-4 py-3 sticky top-0 z-30 shadow-sm">
        <div class="max-w-lg mx-auto flex items-center justify-between">
            <div>
                <p class="font-black text-base leading-tight">{{ $company->name }}</p>
                <p class="text-amber-100 text-xs">{{ $table->name }} · {{ $branch->name }}</p>
            </div>
            <div class="flex items-center gap-2">
            <a href="{{ route('order.history', $table->qr_token) }}"
               class="bg-white/20 hover:bg-white/30 rounded-2xl px-3 py-2 text-xs font-bold transition-colors">
                📋 Pesananku
            </a>
            <button @click="showCart = true"
                    class="relative bg-white/20 hover:bg-white/30 rounded-2xl px-4 py-2 flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-sm font-bold" x-text="cartCount + ' item'"></span>
                <template x-if="cartCount > 0">
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold" x-text="cartCount"></span>
                </template>
            </button>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="max-w-lg mx-auto px-4 pt-4 pb-2">
        <input type="text" x-model="search" placeholder="Cari menu..."
               class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-amber-500 bg-white shadow-sm">
    </div>

    {{-- Product list --}}
    <div class="max-w-lg mx-auto px-4 pb-32">

        {{-- Search results --}}
        <template x-if="search.trim() !== ''">
            <div>
                <div x-show="filteredProducts.length === 0" class="text-center py-16 text-slate-400 text-sm">
                    Menu tidak ditemukan.
                </div>
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="openProduct(product)"
                                :class="!product.is_available ? 'opacity-50 cursor-not-allowed' : 'active:scale-95'"
                                :disabled="!product.is_available"
                                class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 text-left transition-transform">
                            <div class="aspect-[4/3] bg-amber-50 flex items-center justify-center overflow-hidden">
                                <template x-if="product.image_url">
                                    <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image_url">
                                    <span class="text-4xl" x-text="product.category_icon || '🍽️'"></span>
                                </template>
                            </div>
                            <div class="p-3">
                                <p class="text-xs font-semibold text-slate-800 line-clamp-2 leading-tight mb-1" x-text="product.name"></p>
                                <p class="text-xs font-black text-amber-600" x-text="'Rp ' + fmt(product.price)"></p>
                                <template x-if="!product.is_available">
                                    <p class="text-xs text-slate-400 mt-1">Habis</p>
                                </template>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        {{-- Grouped by category --}}
        <template x-if="search.trim() === ''">
            <div class="space-y-6 mt-2">
                <template x-for="group in productsByCategory" :key="group.category_id">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span x-text="group.icon || '🍽️'"></span>
                            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide" x-text="group.name"></h2>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <template x-for="product in group.products" :key="product.id">
                                <button type="button" @click="openProduct(product)"
                                        :class="!product.is_available ? 'opacity-50 cursor-not-allowed' : 'active:scale-95'"
                                        :disabled="!product.is_available"
                                        class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 text-left transition-transform">
                                    <div class="aspect-[4/3] bg-amber-50 flex items-center justify-center overflow-hidden">
                                        <template x-if="product.image_url">
                                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!product.image_url">
                                            <span class="text-4xl" x-text="product.category_icon || '🍽️'"></span>
                                        </template>
                                    </div>
                                    <div class="p-3">
                                        <p class="text-xs font-semibold text-slate-800 line-clamp-2 leading-tight mb-1" x-text="product.name"></p>
                                        <p class="text-xs font-black text-amber-600" x-text="'Rp ' + fmt(product.price)"></p>
                                        <template x-if="product.description">
                                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1" x-text="product.description"></p>
                                        </template>
                                        <template x-if="!product.is_available">
                                            <p class="text-xs text-slate-400 mt-1">Habis</p>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="productsByCategory.length === 0" class="text-center py-16 text-slate-400 text-sm">
                    Belum ada menu tersedia.
                </div>
            </div>
        </template>
    </div>

    {{-- Sticky bottom cart bar --}}
    <template x-if="cartCount > 0">
        <div class="fixed bottom-0 inset-x-0 z-20 p-4 bg-gradient-to-t from-slate-100 to-transparent pointer-events-none">
            <div class="max-w-lg mx-auto pointer-events-auto">
                <button @click="showCart = true"
                        class="w-full bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white rounded-2xl py-4 flex items-center justify-between px-5 shadow-xl transition-colors">
                    <span class="bg-white/20 rounded-xl px-2 py-0.5 text-sm font-bold" x-text="cartCount + ' item'"></span>
                    <span class="font-bold text-base">Lihat Keranjang</span>
                    <span class="font-black text-base" x-text="'Rp ' + fmt(cartTotal)"></span>
                </button>
            </div>
        </div>
    </template>

    {{-- Product Detail Modal --}}
    <div x-show="showProductModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showProductModal = false">
        <div class="bg-white w-full sm:max-w-sm rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-y-auto max-h-[90vh]">

            {{-- Product image --}}
            <template x-if="selectedProduct?.image_url">
                <div class="w-full aspect-video overflow-hidden">
                    <img :src="selectedProduct.image_url" :alt="selectedProduct.name" class="w-full h-full object-cover">
                </div>
            </template>
            <template x-if="!selectedProduct?.image_url">
                <div class="w-full aspect-video bg-amber-50 flex items-center justify-center">
                    <span class="text-6xl" x-text="selectedProduct?.category_icon || '🍽️'"></span>
                </div>
            </template>

            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-black text-slate-800 text-lg leading-tight" x-text="selectedProduct?.name"></h3>
                        <template x-if="selectedProduct?.description">
                            <p class="text-sm text-slate-400 mt-1" x-text="selectedProduct.description"></p>
                        </template>
                    </div>
                    <p class="text-lg font-black text-amber-600 ml-3 shrink-0" x-text="'Rp ' + fmt(itemTotalPrice())"></p>
                </div>

                {{-- Variants --}}
                <template x-if="selectedProduct?.variants?.length > 0">
                    <div class="mb-4">
                        <p class="text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Varian</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="v in selectedProduct.variants" :key="v.id">
                                <button type="button" @click="selectedVariant = v"
                                        :class="selectedVariant?.id === v.id ? 'border-amber-400 bg-amber-50 text-amber-700 font-semibold' : 'border-slate-200 text-slate-600'"
                                        class="border-2 rounded-2xl px-4 py-2 text-sm transition-colors">
                                    <span x-text="v.name + (v.price_adjustment !== 0 ? ' (' + (v.price_adjustment > 0 ? '+' : '') + 'Rp ' + fmt(v.price_adjustment) + ')' : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Modifier Groups --}}
                <template x-for="group in (selectedProduct?.modifier_groups || [])" :key="group.id">
                    <div class="mb-4">
                        <p class="text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">
                            <span x-text="group.name"></span>
                            <span x-show="group.is_required" class="text-red-500 ml-1">*</span>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="opt in group.options" :key="opt.id">
                                <button type="button" @click="toggleModifier(group, opt)"
                                        :class="isModifierSelected(group.id, opt.id) ? 'border-amber-400 bg-amber-50 text-amber-700 font-semibold' : 'border-slate-200 text-slate-600'"
                                        class="border-2 rounded-2xl px-4 py-2 text-sm transition-colors">
                                    <span x-text="opt.name + (opt.price > 0 ? ' +Rp ' + fmt(opt.price) : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Qty --}}
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-bold text-slate-700">Jumlah</p>
                    <div class="flex items-center gap-4">
                        <button @click="itemQty = Math.max(1, itemQty - 1)"
                                class="w-10 h-10 bg-slate-100 hover:bg-slate-200 rounded-2xl font-bold text-slate-700 flex items-center justify-center text-xl">−</button>
                        <span class="text-xl font-black text-slate-800 w-8 text-center" x-text="itemQty"></span>
                        <button @click="itemQty++"
                                class="w-10 h-10 bg-amber-100 hover:bg-amber-200 rounded-2xl font-bold text-amber-700 flex items-center justify-center text-xl">+</button>
                    </div>
                </div>

                {{-- Item notes --}}
                <div class="mb-5">
                    <label class="text-xs font-bold text-slate-700 mb-1.5 block uppercase tracking-wide">Catatan (opsional)</label>
                    <input type="text" x-model="itemNotes" placeholder="mis: tanpa bawang, pedas extra"
                           class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-amber-500">
                </div>

                <button @click="confirmAddToCart()"
                        class="w-full bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-black py-4 rounded-2xl text-base transition-colors">
                    Tambah · <span x-text="'Rp ' + fmt(itemTotalPrice() * itemQty)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Cart / Order Summary Modal --}}
    <div x-show="showCart" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showCart = false">
        <div class="bg-white w-full sm:max-w-sm rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-y-auto max-h-[90vh]">

            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-black text-slate-800 text-lg">Pesanan Saya</h3>
                <button @click="showCart = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-3">
                <template x-for="(item, idx) in cart" :key="item.cartId">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800" x-text="item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></p>
                            <template x-if="item.modifiers?.length > 0">
                                <p class="text-xs text-slate-400" x-text="item.modifiers.map(m => m.option_name).join(', ')"></p>
                            </template>
                            <template x-if="item.notes">
                                <p class="text-xs text-slate-400 italic" x-text="item.notes"></p>
                            </template>
                            <p class="text-xs text-amber-600 font-bold mt-0.5" x-text="'Rp ' + fmt(item.subtotal)"></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="updateQty(item.cartId, -1)"
                                    class="w-7 h-7 bg-slate-100 rounded-xl font-bold text-slate-600 hover:bg-slate-200 flex items-center justify-center">−</button>
                            <span class="text-sm font-bold w-5 text-center" x-text="item.qty"></span>
                            <button @click="updateQty(item.cartId, 1)"
                                    class="w-7 h-7 bg-slate-100 rounded-xl font-bold text-slate-600 hover:bg-slate-200 flex items-center justify-center">+</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="px-5 pb-3 space-y-3">
                {{-- Nama (required) --}}
                <div>
                    <label class="text-xs font-bold text-slate-700 mb-1.5 block">Nama kamu <span class="text-red-500">*</span></label>
                    <input type="text" x-model="customerName" placeholder="Wajib diisi"
                           :class="nameError ? 'border-red-400 bg-red-50' : 'border-slate-200'"
                           @input="nameError = false"
                           class="w-full border rounded-2xl px-4 py-3 text-sm outline-none focus:border-amber-500">
                    <p x-show="nameError" class="text-xs text-red-500 mt-1">Nama wajib diisi.</p>
                </div>

                {{-- Metode pembayaran --}}
                <div>
                    <label class="text-xs font-bold text-slate-700 mb-2 block">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'border-amber-400 bg-amber-50 text-amber-700 font-bold' : 'border-slate-200 text-slate-600'"
                                class="border-2 rounded-2xl py-3 text-sm transition-colors text-center">
                            💵 Tunai<br>
                            <span class="text-xs font-normal">Bayar di kasir</span>
                        </button>
                        <button type="button" @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'border-amber-400 bg-amber-50 text-amber-700 font-bold' : 'border-slate-200 text-slate-600'"
                                class="border-2 rounded-2xl py-3 text-sm transition-colors text-center">
                            📱 QRIS<br>
                            <span class="text-xs font-normal">Scan & bayar</span>
                        </button>
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="text-xs font-bold text-slate-700 mb-1.5 block">Catatan tambahan (opsional)</label>
                    <textarea x-model="orderNotes" rows="2" placeholder="mis: tolong pisahkan saus"
                              class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-amber-500 resize-none"></textarea>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 space-y-1.5">
                <template x-if="{{ $company->tax_rate > 0 ? 'true' : 'false' }}">
                    <div>
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Subtotal</span>
                            <span x-text="'Rp ' + fmt(cartTotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>PPN {{ $company->tax_inclusive ? '(sudah termasuk)' : round($company->tax_rate) . '%' }}</span>
                            <span x-text="'Rp ' + fmt({{ $company->tax_inclusive ? 'Math.round(cartTotal - cartTotal / (1 + ' . ($company->tax_rate / 100) . '))' : 'Math.round(cartTotal * ' . ($company->tax_rate / 100) . ')' }})"></span>
                        </div>
                        <div class="flex justify-between text-base font-black text-amber-600 pt-1.5 border-t border-slate-100 mt-1">
                            <span>Total</span>
                            <span x-text="'Rp ' + fmt({{ $company->tax_inclusive ? 'cartTotal' : 'Math.round(cartTotal * ' . (1 + $company->tax_rate / 100) . ')' }})"></span>
                        </div>
                    </div>
                </template>
                <template x-if="{{ $company->tax_rate > 0 ? 'false' : 'true' }}">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">Total</span>
                        <span class="font-black text-amber-600 text-xl" x-text="'Rp ' + fmt(cartTotal)"></span>
                    </div>
                </template>
            </div>

            <div class="px-5 pb-6">
                <button @click="submitOrder()"
                        :disabled="submitting || cart.length === 0"
                        :class="(submitting || cart.length === 0) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white'"
                        class="w-full font-black py-4 rounded-2xl text-base transition-colors">
                    <span x-text="submitting ? 'Mengirim...' : 'Kirim Pesanan'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- QRIS Payment Screen (shown after submit if QRIS) --}}
    <div x-show="showQris" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="text-4xl mb-2">📱</div>
            <h2 class="font-black text-slate-800 text-xl mb-1">Scan QRIS</h2>

            <p class="text-slate-500 text-sm mb-3">Total: <span class="font-black text-amber-600" x-text="'Rp ' + fmt(orderResult?.total ?? cartTotal)"></span></p>
            {{-- Rejection notice --}}
            <template x-if="rejectionReason">
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-3 text-left">
                    <p class="text-red-700 font-bold text-sm mb-1">❌ Pembayaran Ditolak</p>
                    <p class="text-red-600 text-xs" x-text="rejectionReason"></p>
                    <p class="text-xs text-slate-500 mt-2">Upload ulang bukti transfer yang benar di bawah.</p>
                </div>
            </template>

            <template x-if="qrisImageUrl">
                <div>
                    <div class="flex justify-center mb-3">
                        <img :src="qrisImageUrl" alt="QRIS" class="w-64 h-64 object-contain border border-slate-200 rounded-2xl p-2">
                    </div>
                    <template x-if="!isStaticQris">
                        <p class="text-xs text-slate-400 mb-4">Scan QR di atas dengan e-wallet atau m-banking.<br>Pembayaran akan dikonfirmasi otomatis.</p>
                    </template>
                    <template x-if="isStaticQris">
                        <p class="text-xs text-slate-400 mb-3">Scan QR di atas lalu transfer tepat sesuai nominal.<br>Upload bukti transfer setelah selesai.</p>
                    </template>
                </div>
            </template>
            <template x-if="!qrisImageUrl">
                <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-4 text-center">
                    <p class="text-red-700 font-bold text-sm">❌ Gagal memuat QRIS</p>
                    <p class="text-red-500 text-xs mt-1">Coba ulang atau hubungi kasir.</p>
                </div>
            </template>

            {{-- Upload proof section (static QRIS only) --}}
            <template x-if="isStaticQris">
                <div class="mt-2">
                    <template x-if="proofUploaded && !rejectionReason">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center">
                            <p class="text-emerald-700 font-bold text-sm mb-1">✅ Bukti Diterima</p>
                            <p class="text-emerald-600 text-xs">🔄 Menunggu konfirmasi kasir...</p>
                        </div>
                    </template>
                    <template x-if="!proofUploaded || rejectionReason">
                        <div class="space-y-3">
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-700 block mb-1.5">📎 Upload Bukti Transfer</span>
                                <input type="file" accept="image/*" capture="environment"
                                       @change="proofFile = $event.target.files[0]; rejectionReason = null"
                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                            </label>
                            <button @click="uploadProof()"
                                    :disabled="!proofFile || proofUploading"
                                    :class="(!proofFile || proofUploading) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                                    class="w-full font-semibold py-3 rounded-2xl text-sm transition-colors">
                                <span x-text="proofUploading ? 'Mengupload...' : 'Kirim Bukti Transfer'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Xendit simulate button (dev/test only) --}}
            <template x-if="xenditTestMode && !isStaticQris">
                <div class="mt-3">
                    <button @click="simulateXenditPayment()"
                            class="w-full border border-slate-300 text-slate-600 text-xs font-semibold py-2.5 rounded-2xl hover:bg-slate-50 transition-colors">
                        🧪 Simulate Pembayaran (Test)
                    </button>
                </div>
            </template>

            <template x-if="!isStaticQris">
                <p class="text-xs text-slate-400 mt-3">Pembayaran akan terdeteksi otomatis setelah berhasil.</p>
            </template>
            <p class="text-xs text-emerald-600 font-semibold mt-2" x-show="qrisPolling && !isStaticQris">🔄 Menunggu konfirmasi pembayaran...</p>
        </div>
    </div>

    {{-- Success / Order Detail Overlay --}}
    <div x-show="showSuccess" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white w-full sm:max-w-sm rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-y-auto max-h-[92vh]">

            {{-- Confetti header --}}
            <div class="bg-gradient-to-br from-amber-400 to-amber-600 text-white text-center px-6 pt-6 pb-5 rounded-t-3xl sm:rounded-t-3xl">
                <div class="text-5xl mb-2">🎉</div>
                <h2 class="font-black text-xl">Pesanan Terkirim!</h2>
                <p class="text-amber-100 text-sm mt-1" x-text="'Hei ' + (orderResult?.customer_name || 'kamu') + (orderResult?.preferred_payment === 'qris' ? ', pembayaran diterima! Pesananmu sedang diproses.' : ', pesananmu sudah masuk. Silakan bayar di kasir.')"></p>
            </div>

            {{-- Order details --}}
            <div class="p-5 space-y-4">
                {{-- Info row --}}
                <div class="flex justify-between text-xs text-slate-500">
                    <span x-text="'📍 ' + (orderResult?.table_name || '—')"></span>
                    <span :class="orderResult?.preferred_payment === 'qris' ? 'text-blue-600 font-semibold' : 'text-green-600 font-semibold'"
                          x-text="orderResult?.preferred_payment === 'qris' ? '📱 QRIS' : '💵 Bayar di kasir'"></span>
                </div>

                {{-- Items --}}
                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <div class="bg-slate-50 px-4 py-2 text-xs font-bold text-slate-600 uppercase tracking-wide">Detail Pesanan</div>
                    <div class="divide-y divide-slate-100">
                        <template x-for="item in (orderResult?.items || [])" :key="item.product_name + item.qty">
                            <div class="px-4 py-3 flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 leading-tight"
                                       x-text="item.qty + '× ' + item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></p>
                                    <template x-if="item.modifiers">
                                        <p class="text-xs text-slate-400" x-text="item.modifiers"></p>
                                    </template>
                                    <template x-if="item.notes">
                                        <p class="text-xs text-slate-400 italic" x-text="item.notes"></p>
                                    </template>
                                </div>
                                <span class="text-sm font-bold text-amber-600 shrink-0" x-text="'Rp ' + fmt(item.subtotal)"></span>
                            </div>
                        </template>
                    </div>
                    <div class="border-t border-slate-200 px-4 py-3 flex justify-between items-center">
                        <span class="text-sm font-black text-slate-800">Total</span>
                        <span class="text-base font-black text-amber-600" x-text="'Rp ' + fmt(orderResult?.total)"></span>
                    </div>
                </div>

                <template x-if="orderResult?.notes">
                    <p class="text-xs text-slate-400 italic px-1" x-text="'Catatan: ' + orderResult.notes"></p>
                </template>

                <p class="text-xs text-center text-slate-400">Silakan tunggu, kami segera siapkan pesananmu. ☕</p>

                {{-- CTA --}}
                <button @click="showSuccess = false; cart = []"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-3.5 rounded-2xl text-sm transition-colors">
                    + Pesan Lagi
                </button>
            </div>
        </div>
    </div>

</div>

<script>
const PRODUCTS = @json($products);
const CATEGORIES = @json($categories);
const SUBMIT_URL = '{{ route('order.submit', $table->qr_token) }}';
const CSRF = '{{ csrf_token() }}';

function qrMenu() {
    return {
        products: PRODUCTS,
        categories: CATEGORIES,
        search: '',

        // Product modal
        showProductModal: false,
        selectedProduct: null,
        selectedVariant: null,
        selectedModifiers: {},
        itemNotes: '',
        itemQty: 1,

        // Cart
        cart: [],
        showCart: false,
        customerName: '',
        orderNotes: '',
        paymentMethod: 'cash',
        nameError: false,
        submitting: false,

        // Post-submit screens
        showQris: false,
        showSuccess: false,
        orderResult: null,
        qrisImageUrl: null,
        qrisPolling: false,
        _qrisTimer: null,
        _qrisOrderId: null,
        isStaticQris: false,
        xenditTestMode: false,
        proofFile: null,
        proofUploading: false,
        proofUploaded: false,
        rejectionReason: null,

        get filteredProducts() {
            const q = this.search.toLowerCase();
            return this.products.filter(p => p.name.toLowerCase().includes(q));
        },

        get productsByCategory() {
            const map = new Map();
            for (const cat of this.categories) {
                map.set(cat.id, { category_id: cat.id, name: cat.name, icon: cat.icon || '', products: [] });
            }
            map.set(null, { category_id: null, name: 'Lainnya', icon: '📦', products: [] });
            for (const p of this.products) {
                const key = p.category_id ?? null;
                if (map.has(key)) map.get(key).products.push(p);
                else map.get(null).products.push(p);
            }
            return [...map.values()].filter(g => g.products.length > 0);
        },

        get cartCount() {
            return this.cart.reduce((s, i) => s + i.qty, 0);
        },

        get cartTotal() {
            return this.cart.reduce((s, i) => s + i.subtotal, 0);
        },

        openProduct(product) {
            if (!product.is_available) return;
            if (product.variants.length === 0 && product.modifier_groups.length === 0) {
                this.addToCart(product, null, {}, 1, '');
                return;
            }
            this.selectedProduct = product;
            this.selectedVariant = product.variants.length > 0 ? product.variants[0] : null;
            this.selectedModifiers = {};
            this.itemNotes = '';
            this.itemQty = 1;
            this.showProductModal = true;
        },

        itemTotalPrice() {
            if (!this.selectedProduct) return 0;
            const base = this.selectedProduct.price + (this.selectedVariant?.price_adjustment || 0);
            const modTotal = Object.values(this.selectedModifiers).flat().reduce((s, m) => s + m.price, 0);
            return base + modTotal;
        },

        toggleModifier(group, option) {
            if (!this.selectedModifiers[group.id]) this.selectedModifiers[group.id] = [];
            const list = this.selectedModifiers[group.id];
            const idx = list.findIndex(m => m.id === option.id);
            if (idx >= 0) list.splice(idx, 1);
            else if (group.is_multiple) list.push(option);
            else this.selectedModifiers[group.id] = [option];
        },

        isModifierSelected(groupId, optionId) {
            return (this.selectedModifiers[groupId] || []).some(m => m.id === optionId);
        },

        confirmAddToCart() {
            if (!this.selectedProduct) return;
            this.addToCart(this.selectedProduct, this.selectedVariant, this.selectedModifiers, this.itemQty, this.itemNotes);
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
            } else {
                this.cart.push({
                    cartId: performance.now() + Math.random(),
                    product_id: product.id,
                    product_variant_id: variant?.id || null,
                    product_name: product.name,
                    variant_name: variant?.name || null,
                    unit_price: unitPrice,
                    qty,
                    subtotal: unitPrice * qty,
                    notes,
                    modifiers: modList,
                });
            }
            this.showProductModal = false;
        },

        updateQty(cartId, delta) {
            const item = this.cart.find(i => i.cartId === cartId);
            if (!item) return;
            item.qty = Math.max(0, item.qty + delta);
            if (item.qty === 0) {
                this.cart = this.cart.filter(i => i.cartId !== cartId);
            } else {
                item.subtotal = item.unit_price * item.qty;
            }
        },

        async submitOrder() {
            if (this.cart.length === 0 || this.submitting) return;
            if (!this.customerName.trim()) { this.nameError = true; return; }

            this.submitting = true;

            const payload = {
                customer_name:     this.customerName.trim(),
                preferred_payment: this.paymentMethod,
                notes:             this.orderNotes || null,
                items: this.cart.map(item => ({
                    product_id:         item.product_id,
                    product_variant_id: item.product_variant_id || null,
                    qty:                item.qty,
                    notes:              item.notes || null,
                    modifiers:          item.modifiers.map(m => ({ modifier_option_id: m.id })),
                })),
            };

            try {
                const res = await fetch(SUBMIT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (res.ok) {
                    this.orderResult    = data;
                    this.showCart       = false;
                    if (this.paymentMethod === 'qris') {
                        this.qrisImageUrl  = data.qris_image_url;
                        this.isStaticQris  = data.is_static_qris ?? false;
                        this.xenditTestMode= data.xendit_test_mode ?? false;
                        this.proofFile     = null;
                        this.proofUploading= false;
                        this.proofUploaded = false;
                        this.rejectionReason = null;
                        this.showQris      = true;
                        this.startQrisPolling(data.order_id);
                    } else {
                        this.showSuccess = true;
                    }
                } else {
                    alert(data.message || 'Gagal mengirim pesanan.');
                }
            } catch (e) {
                alert('Gagal terhubung ke server. Periksa koneksi internet.');
            }

            this.submitting = false;
        },

        startQrisPolling(orderId) {
            if (this._qrisTimer) clearInterval(this._qrisTimer);
            this.qrisPolling   = true;
            this._qrisOrderId  = orderId;
            const token        = '{{ $table->qr_token }}';
            let attempts       = 0;
            const maxAttempts  = 150; // ~5 minutes at 2s interval

            this._qrisTimer = setInterval(async () => {
                attempts++;
                if (attempts > maxAttempts) {
                    clearInterval(this._qrisTimer);
                    this.qrisPolling = false;
                    return;
                }
                try {
                    const res = await fetch(`/order/${token}/payment-status/${orderId}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (res.ok) {
                        const d = await res.json();
                        if (d.paid) {
                            clearInterval(this._qrisTimer);
                            this.qrisPolling  = false;
                            this.showQris     = false;
                            this.showSuccess  = true;
                        } else if (d.rejected) {
                            clearInterval(this._qrisTimer);
                            this.qrisPolling    = false;
                            this.rejectionReason= d.rejection_reason || 'Pembayaran ditolak';
                            this.proofUploaded  = false;
                            this.proofFile      = null;
                        }
                    }
                } catch (_) {}
            }, 2000);
        },

        async uploadProof() {
            if (!this.proofFile || this.proofUploading) return;
            this.proofUploading = true;
            const token = '{{ $table->qr_token }}';
            const orderId = this._qrisOrderId;
            const form = new FormData();
            form.append('proof', this.proofFile);
            form.append('_token', CSRF);
            try {
                const res = await fetch(`/order/${token}/upload-proof/${orderId}`, {
                    method: 'POST',
                    body: form,
                });
                if (res.ok) {
                    this.proofUploaded = true;
                } else {
                    const d = await res.json().catch(() => ({}));
                    alert(d.message || 'Gagal upload bukti. Coba lagi.');
                }
            } catch (_) {
                alert('Gagal terhubung ke server.');
            }
            this.proofUploading = false;
        },

        async simulateXenditPayment() {
            const token = '{{ $table->qr_token }}';
            const orderId = this._qrisOrderId;
            try {
                const res = await fetch(`/order/${token}/simulate-xendit/${orderId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                if (res.ok) {
                    alert('Simulate sukses! Menunggu webhook...');
                } else {
                    alert('Simulate gagal.');
                }
            } catch (_) {
                alert('Gagal terhubung.');
            }
        },

        fmt(n) {
            return Number(n || 0).toLocaleString('id-ID');
        },
    };
}
</script>
</body>
</html>
