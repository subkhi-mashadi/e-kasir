<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan — {{ $table->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-50 min-h-screen">

<div x-data="orderHistory()" x-init="init()">

    {{-- Top bar --}}
    <div class="bg-amber-500 text-white px-4 py-3 sticky top-0 z-10 shadow-sm">
        <div class="max-w-lg mx-auto flex items-center justify-between">
            <div>
                <p class="font-black text-base leading-tight">Riwayat Pesanan</p>
                <p class="text-amber-100 text-xs">{{ $table->name }} · {{ $table->branch->name }}</p>
            </div>
            <a href="{{ route('order.show', $table->qr_token) }}"
               class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-3 py-2 rounded-xl transition-colors">
                + Pesan Lagi
            </a>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-5 space-y-4">

        <div x-show="orders.length === 0 && !loading" class="text-center py-16">
            <div class="text-4xl mb-3">🍽️</div>
            <p class="text-slate-500 text-sm">Belum ada pesanan hari ini.</p>
            <a href="{{ route('order.show', $table->qr_token) }}"
               class="inline-block mt-4 bg-amber-500 text-white text-sm font-bold px-5 py-2.5 rounded-2xl">
                Lihat Menu
            </a>
        </div>

        <div x-show="loading && orders.length === 0" class="text-center py-16 text-slate-400 text-sm">
            Memuat...
        </div>

        <template x-for="order in orders" :key="order.id">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                {{-- Order header --}}
                <div class="px-4 py-3 flex items-start justify-between border-b border-slate-100">
                    <div>
                        <p class="text-sm font-bold text-slate-800" x-text="order.customer_name || 'Pelanggan'"></p>
                        <p class="text-xs text-slate-400" x-text="order.created_at"></p>
                    </div>
                    {{-- Status badge --}}
                    <span :class="statusClass(order)" class="text-xs font-bold px-3 py-1 rounded-full" x-text="statusLabel(order)"></span>
                </div>

                {{-- Items --}}
                <div class="px-4 py-3 space-y-1.5">
                    <template x-for="item in order.items" :key="item.product_name + item.qty">
                        <div class="flex justify-between text-sm">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-slate-700" x-text="item.qty + '× ' + item.product_name + (item.variant_name ? ' · ' + item.variant_name : '')"></span>
                                <template x-if="item.modifiers">
                                    <span class="text-xs text-slate-400 ml-1" x-text="'(' + item.modifiers + ')'"></span>
                                </template>
                            </div>
                            <span class="text-xs text-amber-600 font-bold ml-2 shrink-0" x-text="'Rp ' + fmt(item.subtotal)"></span>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-4 py-2.5 bg-slate-50 flex justify-between items-center">
                    <span class="text-xs text-slate-500" x-text="order.preferred_payment === 'qris' ? '📱 QRIS' : '💵 Tunai'"></span>
                    <span class="text-sm font-black text-amber-600" x-text="'Rp ' + fmt(order.total)"></span>
                </div>

                {{-- Upload bukti transfer (menunggu konfirmasi kasir) --}}
                <template x-if="order.status === 'open' && order.preferred_payment === 'qris'">
                    <div class="px-4 py-3 border-t border-slate-100 space-y-2">
                        <template x-if="order.rejection_reason">
                            <p class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2">
                                ❌ Bukti ditolak: <span x-text="order.rejection_reason"></span> — upload ulang di bawah ini.
                            </p>
                        </template>
                        <template x-if="order.payment_proof_url && !order.rejection_reason">
                            <p class="text-xs text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2">
                                ✅ Bukti diterima — menunggu konfirmasi kasir.
                            </p>
                        </template>
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-700 block mb-1.5" x-text="order.payment_proof_url ? '📎 Upload Ulang Bukti' : '📎 Upload Bukti Transfer'"></span>
                            <input type="file" accept="image/*"
                                   @change="proofFiles[order.id] = $event.target.files[0]"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                        </label>
                        <button @click="uploadProof(order.id)"
                                :disabled="!proofFiles[order.id] || uploading[order.id]"
                                :class="(!proofFiles[order.id] || uploading[order.id]) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                                class="w-full font-semibold py-2.5 rounded-2xl text-xs transition-colors">
                            <span x-text="uploading[order.id] ? 'Mengupload...' : 'Kirim Bukti Transfer'"></span>
                        </button>
                    </div>
                </template>

                {{-- Kitchen status progress --}}
                <div x-show="order.status === 'paid'" class="px-4 py-3 border-t border-slate-100">
                    <div class="flex items-center gap-1.5">
                        <template x-for="(step, idx) in kitchenSteps" :key="step.key">
                            <div class="flex items-center gap-1.5 flex-1 min-w-0">
                                <div :class="stepDone(order, step.key) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400'"
                                     class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-colors">
                                    <span x-text="stepDone(order, step.key) ? '✓' : (idx + 1)"></span>
                                </div>
                                <span :class="stepDone(order, step.key) ? 'text-emerald-700' : 'text-slate-400'"
                                      class="text-xs font-medium truncate transition-colors" x-text="step.label"></span>
                                <template x-if="idx < kitchenSteps.length - 1">
                                    <div :class="stepDone(order, kitchenSteps[idx + 1]?.key) ? 'bg-emerald-400' : 'bg-slate-200'"
                                         class="h-0.5 flex-1 transition-colors"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Ready alert --}}
                <template x-if="order.kitchen_status === 'ready'">
                    <div class="px-4 py-3 bg-emerald-50 border-t border-emerald-200 flex items-center gap-2 text-emerald-700 text-sm font-bold">
                        <span class="text-lg">🛎️</span>
                        <span>Pesananmu siap! Sedang diantarkan pelayan.</span>
                    </div>
                </template>
            </div>
        </template>
    </div>

</div>

<script>
const HISTORY_URL = '{{ route('order.history', $table->qr_token) }}';
const TOKEN = '{{ $table->qr_token }}';
const CSRF = '{{ csrf_token() }}';

function orderHistory() {
    return {
        orders: @json($orders),
        loading: false,
        proofFiles: {},
        uploading: {},

        kitchenSteps: [
            { key: 'pending',    label: 'Dikonfirmasi' },
            { key: 'preparing',  label: 'Diproses' },
            { key: 'ready',      label: 'Siap Antar' },
            { key: 'delivered',  label: 'Diantar' },
        ],

        init() {
            setInterval(() => this.refresh(), 10000);
        },

        async refresh() {
            try {
                const res = await fetch(HISTORY_URL, { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    this.orders = data.orders;
                }
            } catch (_) {}
        },

        async uploadProof(orderId) {
            const file = this.proofFiles[orderId];
            if (!file) return;

            this.uploading[orderId] = true;
            try {
                const form = new FormData();
                form.append('proof', file);
                form.append('_token', CSRF);

                const res = await fetch(`/order/${TOKEN}/upload-proof/${orderId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: form,
                });

                if (res.ok) {
                    delete this.proofFiles[orderId];
                    await this.refresh();
                } else {
                    alert('Gagal upload bukti, coba lagi.');
                }
            } catch (_) {
                alert('Gagal upload bukti, coba lagi.');
            } finally {
                this.uploading[orderId] = false;
            }
        },

        statusLabel(order) {
            if (order.status === 'failed') return 'Kadaluarsa';
            if (order.status === 'cancelled') return 'Dibatalkan';
            if (order.status === 'open') return 'Menunggu Kasir';
            // paid
            const map = {
                pending:   'Dikonfirmasi',
                preparing: 'Diproses Dapur',
                ready:     '🛎️ Siap Diantar!',
                delivered: 'Selesai',
            };
            return map[order.kitchen_status] || 'Dikonfirmasi';
        },

        statusClass(order) {
            if (order.status === 'failed') return 'bg-red-100 text-red-600';
            if (order.status === 'cancelled') return 'bg-red-100 text-red-600';
            if (order.status === 'open') return 'bg-amber-100 text-amber-700';
            if (order.kitchen_status === 'ready') return 'bg-emerald-500 text-white animate-pulse';
            if (order.kitchen_status === 'delivered') return 'bg-emerald-100 text-emerald-700';
            if (order.kitchen_status === 'preparing') return 'bg-blue-100 text-blue-700';
            return 'bg-slate-100 text-slate-600';
        },

        stepDone(order, key) {
            const order_seq = { pending: 1, preparing: 2, ready: 3, delivered: 4 };
            return (order_seq[order.kitchen_status] || 0) >= (order_seq[key] || 0);
        },

        fmt(n) {
            return Number(n || 0).toLocaleString('id-ID');
        },
    };
}
</script>
</body>
</html>
