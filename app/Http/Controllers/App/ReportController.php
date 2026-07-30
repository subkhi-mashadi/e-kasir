<?php

namespace App\Http\Controllers\App;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private function hasReports(): bool
    {
        return (bool) auth()->user()->company?->hasFeature('feature_advanced_reports');
    }

    private function featureLockedResponse(): \Illuminate\View\View
    {
        return view('errors.feature-locked', [
            'featureName' => 'Laporan Lanjutan',
            'description' => 'Akses laporan penjualan harian, laporan per kasir, dan ekspor data ke Excel maupun PDF.',
            'benefits'    => [
                'Laporan penjualan dengan grafik per hari',
                'Laporan per kasir untuk evaluasi tim',
                'Ekspor ke Excel & PDF siap cetak',
                'Top 10 produk terlaris',
            ],
        ]);
    }

    public function sales(Request $request)
    {
        if (!$this->hasReports()) {
            return $this->featureLockedResponse();
        }
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $dari    = $request->filled('dari')   ? $request->dari   : today()->subDays(29)->format('Y-m-d');
        $sampai  = $request->filled('sampai') ? $request->sampai : today()->format('Y-m-d');

        $base = Order::where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai);

        $totalPendapatan = (clone $base)->sum('total');
        $totalTransaksi  = (clone $base)->count();
        $rataRata        = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        // Daily breakdown
        $perHari = (clone $base)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as transaksi, SUM(total) as pendapatan')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('tanggal')
            ->get();

        // Payment method breakdown
        $perMetode = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', '>=', $dari)
            ->whereDate('orders.created_at', '<=', $sampai)
            ->selectRaw('payments.method, COUNT(*) as jumlah, SUM(payments.amount) as total')
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get();

        // Top 10 products
        $topProduk = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', '>=', $dari)
            ->whereDate('orders.created_at', '<=', $sampai)
            ->selectRaw('order_items.product_name, SUM(order_items.qty) as qty, SUM(order_items.subtotal) as pendapatan')
            ->groupBy('order_items.product_name')
            ->orderByDesc('pendapatan')
            ->limit(10)
            ->get();

        return view('app.reports.sales', compact(
            'dari', 'sampai',
            'totalPendapatan', 'totalTransaksi', 'rataRata',
            'perHari', 'perMetode', 'topProduk'
        ));
    }

    public function perKasir(Request $request)
    {
        if (!$this->hasReports()) {
            return $this->featureLockedResponse();
        }
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $dari     = $request->filled('dari')   ? $request->dari   : today()->subDays(29)->format('Y-m-d');
        $sampai   = $request->filled('sampai') ? $request->sampai : today()->format('Y-m-d');

        $perKasir = Order::with('user:id,name')
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai)
            ->selectRaw('user_id, COUNT(*) as transaksi, SUM(total) as pendapatan')
            ->groupBy('user_id')
            ->orderByDesc('pendapatan')
            ->get();

        return view('app.reports.per-kasir', compact('dari', 'sampai', 'perKasir'));
    }

    public function exportExcel(Request $request)
    {
        abort_unless($this->hasReports(), 403);
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $dari   = $request->filled('dari')   ? $request->dari   : today()->subDays(29)->format('Y-m-d');
        $sampai = $request->filled('sampai') ? $request->sampai : today()->format('Y-m-d');

        $base = Order::where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai);

        $totalPendapatan = (clone $base)->sum('total');
        $totalTransaksi  = (clone $base)->count();
        $rataRata        = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        $perHari = (clone $base)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as transaksi, SUM(total) as pendapatan')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('tanggal')
            ->get();

        $topProduk = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', '>=', $dari)
            ->whereDate('orders.created_at', '<=', $sampai)
            ->selectRaw('order_items.product_name, SUM(order_items.qty) as qty, SUM(order_items.subtotal) as pendapatan')
            ->groupBy('order_items.product_name')
            ->orderByDesc('pendapatan')
            ->limit(10)
            ->get();

        $filename = 'laporan-penjualan-' . $dari . '-' . $sampai . '.xlsx';

        return Excel::download(
            new SalesReportExport(
                (float) $totalPendapatan,
                (int)   $totalTransaksi,
                (float) $rataRata,
                $dari,
                $sampai,
                $perHari,
                $topProduk,
            ),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        abort_unless($this->hasReports(), 403);
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $dari   = $request->filled('dari')   ? $request->dari   : today()->subDays(29)->format('Y-m-d');
        $sampai = $request->filled('sampai') ? $request->sampai : today()->format('Y-m-d');

        $base = Order::where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai);

        $totalPendapatan = (clone $base)->sum('total');
        $totalTransaksi  = (clone $base)->count();
        $rataRata        = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        $perHari = (clone $base)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as transaksi, SUM(total) as pendapatan')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('tanggal')
            ->get();

        $topProduk = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', '>=', $dari)
            ->whereDate('orders.created_at', '<=', $sampai)
            ->selectRaw('order_items.product_name, SUM(order_items.qty) as qty, SUM(order_items.subtotal) as pendapatan')
            ->groupBy('order_items.product_name')
            ->orderByDesc('pendapatan')
            ->limit(10)
            ->get();

        $branch      = \App\Models\Branch::find($branchId);
        $companyName = $branch?->company?->name ?? 'E-Kasir';
        $branchName  = $branch?->name ?? '—';

        $filename = 'laporan-penjualan-' . $dari . '-' . $sampai . '.pdf';

        return Pdf::loadView('app.reports.sales-pdf', compact(
            'dari', 'sampai',
            'totalPendapatan', 'totalTransaksi', 'rataRata',
            'perHari', 'topProduk',
            'companyName', 'branchName',
        ))
        ->setPaper('a4', 'portrait')
        ->download($filename);
    }
}
