@extends('layouts.app')

@section('title', 'Langganan')
@section('page-title', 'Kelola Langganan')
@section('page-subtitle', $company?->name)

@push('scripts')
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
function checkout() {
    const el = document.querySelector('[x-data]');
    const comp = Alpine.$data(el);
    if (!comp.selectedPlan || comp.processing) return;
    comp.processing = true;

    fetch('{{ route('subscription.checkout') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({ plan_id: comp.selectedPlan, period: comp.period }),
    })
    .then(r => r.json())
    .then(data => {
        snap.pay(data.snap_token, {
            onSuccess: () => { window.location.href = '{{ route('subscription.callback') }}?invoice_id=' + data.invoice_id; },
            onPending: () => { comp.processing = false; },
            onError:   () => { comp.processing = false; alert('Pembayaran gagal.'); },
            onClose:   () => { comp.processing = false; },
        });
    })
    .catch(() => { comp.processing = false; alert('Gagal terhubung ke server.'); });
}

function payPending(token, invoiceId) {
    snap.pay(token, {
        onSuccess: () => { window.location.href = '{{ route('subscription.callback') }}?invoice_id=' + invoiceId; },
        onClose:   () => {},
    });
}
</script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Current subscription --}}
    @if ($subscription)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Paket Aktif</p>
                <h2 class="text-xl font-black text-slate-800">{{ $subscription->plan?->name ?? '—' }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    @if ($subscription->status === 'trial')
                        <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs font-semibold">Trial</span>
                        — berakhir {{ $subscription->trial_ends_at?->translatedFormat('d M Y') ?? '-' }}
                    @elseif ($subscription->status === 'active')
                        <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-semibold">Aktif</span>
                        — berakhir {{ $subscription->ends_at?->translatedFormat('d M Y') ?? '-' }}
                    @elseif ($subscription->status === 'expired')
                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-semibold">Berakhir</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full text-xs font-semibold">{{ ucfirst($subscription->status) }}</span>
                    @endif
                </p>
            </div>
            <div class="text-right text-sm text-slate-400">
                <p>{{ $subscription->period === 'yearly' ? 'Tahunan' : 'Bulanan' }}</p>
            </div>
        </div>

        @if ($subscription->plan)
        <div class="grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-slate-100 text-center">
            <div>
                <p class="text-xs text-slate-400">Cabang</p>
                <p class="font-bold text-slate-700">{{ $subscription->plan->max_branches == 999 ? '∞' : $subscription->plan->max_branches }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Pengguna</p>
                <p class="font-bold text-slate-700">{{ $subscription->plan->max_users == 999 ? '∞' : $subscription->plan->max_users }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Produk</p>
                <p class="font-bold text-slate-700">{{ $subscription->plan->max_products == 9999 ? '∞' : $subscription->plan->max_products }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Plan picker --}}
    <div x-data="{ period: 'monthly', selectedPlan: null, processing: false }" class="space-y-6">

        {{-- Period toggle --}}
        <div class="flex items-center gap-3">
            <span class="text-sm font-semibold text-slate-700">Periode:</span>
            <button @click="period = 'monthly'" :class="period === 'monthly' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                    class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors">Bulanan</button>
            <button @click="period = 'yearly'" :class="period === 'yearly' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200'"
                    class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors">
                Tahunan
                <span class="ml-1 text-xs bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full font-semibold">Hemat ~17%</span>
            </button>
        </div>

        {{-- Plan cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($plans as $plan)
            <div @click="selectedPlan = {{ $plan->id }}"
                 :class="selectedPlan === {{ $plan->id }} ? 'ring-2 ring-amber-500 border-amber-300' : 'border-slate-200 hover:border-amber-200'"
                 class="bg-white rounded-2xl border-2 p-5 cursor-pointer transition-all relative">

                @if ($plan->slug === 'pro')
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full">Populer</div>
                @endif

                <h3 class="font-black text-slate-800 text-lg">{{ $plan->name }}</h3>
                <div class="mt-2 mb-4">
                    <span class="text-2xl font-black text-amber-600">
                        <span x-text="period === 'yearly' ? 'Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}' : 'Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}'"></span>
                    </span>
                    <span class="text-slate-400 text-sm" x-text="period === 'yearly' ? '/tahun' : '/bulan'"></span>
                </div>

                <ul class="space-y-1.5 text-sm text-slate-600">
                    <li>{{ $plan->max_branches == 999 ? 'Cabang tak terbatas' : $plan->max_branches . ' Cabang' }}</li>
                    <li>{{ $plan->max_users == 999 ? 'Pengguna tak terbatas' : $plan->max_users . ' Pengguna' }}</li>
                    <li>{{ $plan->max_products == 9999 ? 'Produk tak terbatas' : $plan->max_products . ' Produk' }}</li>
                    @if ($plan->feature_qr_ordering)
                    <li class="text-emerald-600">✓ QR Ordering</li>
                    @endif
                    @if ($plan->feature_advanced_reports)
                    <li class="text-emerald-600">✓ Laporan Lanjutan</li>
                    @endif
                    @if ($plan->feature_multi_device)
                    <li class="text-emerald-600">✓ Multi Device</li>
                    @endif
                </ul>
            </div>
            @endforeach
        </div>

        {{-- Checkout button --}}
        <div x-show="selectedPlan !== null">
            <button @click="checkout()"
                    :disabled="processing"
                    :class="processing ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                    class="w-full py-3 rounded-2xl font-bold text-sm transition-colors">
                <span x-text="processing ? 'Memproses...' : 'Bayar Sekarang'"></span>
            </button>
        </div>
    </div>

    {{-- Pending invoice --}}
    @if ($pendingInvoice)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <p class="text-sm font-semibold text-amber-800 mb-1">Invoice Menunggu Pembayaran</p>
        <p class="text-xs text-amber-700">Invoice <span class="font-mono">{{ $pendingInvoice->invoice_no }}</span> — Rp {{ number_format($pendingInvoice->amount, 0, ',', '.') }}</p>
        @if ($pendingInvoice->midtrans_snap_token)
        <button onclick="payPending('{{ $pendingInvoice->midtrans_snap_token }}', {{ $pendingInvoice->id }})"
                class="mt-3 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            Lanjutkan Pembayaran
        </button>
        @endif
    </div>
    @endif

</div>
@endsection
