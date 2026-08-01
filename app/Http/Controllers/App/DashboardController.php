<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->activeBranchId();
        $user     = auth()->user();

        $todayOrders = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->where('status', 'paid')
            ->count();

        $todayRevenue = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('total');

        $lowStock = Product::withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
            ->whereHas('inventories', fn ($q) => $q
                ->where('branch_id', $branchId)
                ->whereRaw('qty <= min_qty')
                ->where('min_qty', '>', 0)
            )
            ->where('company_id', $user->company_id)
            ->where('is_active', true)
            ->count();

        $currentBranch = Branch::find($branchId);
        $allBranches   = $user->hasRole('owner')
            ? Branch::where('company_id', $user->company_id)->where('is_active', true)->get()
            : collect();

        return view('app.dashboard', compact(
            'todayOrders', 'todayRevenue', 'lowStock',
            'currentBranch', 'allBranches'
        ));
    }
}
