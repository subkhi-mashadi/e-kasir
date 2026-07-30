<?php

use App\Http\Controllers\Api\SyncOrderController;
use App\Http\Controllers\Subscription\BillingController;
use App\Http\Controllers\Auth\BranchSelectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Landing\LandingController;
use App\Http\Controllers\Landing\DemoController;
use App\Http\Controllers\App\BranchController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\CustomerController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\OrderController;
use App\Http\Controllers\App\ReportController;
use App\Http\Controllers\App\InventoryController;
use App\Http\Controllers\App\ModifierGroupController;
use App\Http\Controllers\App\ProductController;
use App\Http\Controllers\App\StaffController;
use App\Http\Controllers\App\QrisVerificationController;
use App\Http\Controllers\App\TableController;
use App\Http\Controllers\Kitchen\KitchenController;
use App\Http\Controllers\Order\QrOrderController;
use App\Http\Controllers\Payment\MidtransWebhookController;
use App\Http\Controllers\Payment\XenditWebhookController;
use App\Http\Controllers\POS\POSController;
use Illuminate\Support\Facades\Route;

// ── Landing ───────────────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/demo', [DemoController::class, 'launch'])->name('demo')->middleware('throttle:20,1');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,   'show'])->name('login');
    Route::post('/login',   [LoginController::class,   'login'])->name('login.post')->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class,'show'])->name('register');
    Route::post('/register',[RegisterController::class,'store'])->name('register.post')->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/branch-select', [BranchSelectController::class, 'show'])->name('branch.select');
    Route::post('/branch-select', [BranchSelectController::class, 'select'])->name('branch.select.post');
    Route::post('/branch-change', [BranchSelectController::class, 'changeBranch'])->name('branch.change');
});

// ── App (tenant) ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant.active', 'branch.selected'])
    ->prefix('app')
    ->name('app.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Branches
        Route::resource('branches', BranchController::class)->except(['show']);

        // Products
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
        Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

        // Modifier groups
        Route::resource('modifiers', ModifierGroupController::class)->except(['show']);
        Route::post('modifiers/{modifier}/options', [ModifierGroupController::class, 'storeOption'])->name('modifiers.options.store');
        Route::delete('modifiers/{modifier}/options/{option}', [ModifierGroupController::class, 'destroyOption'])->name('modifiers.options.destroy');

        // Tables
        Route::resource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate-qr', [TableController::class, 'regenerateQr'])->name('tables.regenerate-qr');
        Route::get('tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::match(['POST', 'PATCH'], 'inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        // Orders history
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales/export/excel', [ReportController::class, 'exportExcel'])->name('reports.sales.excel');
        Route::get('/reports/sales/export/pdf',   [ReportController::class, 'exportPdf'])->name('reports.sales.pdf');
        Route::get('/reports/per-kasir', [ReportController::class, 'perKasir'])->name('reports.per-kasir');

        // Customers
        Route::resource('customers', CustomerController::class)->except(['show']);

        // Staff management (owner only)
        Route::resource('staff', StaffController::class)->except(['show']);
        Route::get('qris-verification', [QrisVerificationController::class, 'index'])->name('app.qris-verification.index');
        Route::post('qris-verification/{order}/approve', [QrisVerificationController::class, 'approve'])->name('app.qris-verification.approve');
        Route::post('qris-verification/{order}/reject', [QrisVerificationController::class, 'reject'])->name('app.qris-verification.reject');

        // Settings (owner only)
        Route::get('/settings/payment', [SettingsController::class, 'payment'])->name('settings.payment');
        Route::post('/settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment.update');
    });

// ── POS (Kasir) ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant.active', 'branch.selected'])
    ->prefix('pos')
    ->name('pos.')
    ->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/products', [POSController::class, 'products'])->name('products');
        Route::post('/orders', [POSController::class, 'store'])->name('orders.store');
        Route::get('/orders', [POSController::class, 'orders'])->name('orders.index');
        Route::get('/orders/incoming', [POSController::class, 'incomingOrders'])->name('orders.incoming');
        Route::get('/orders/ready', [POSController::class, 'readyOrders'])->name('orders.ready');
        Route::get('/orders/{order}/receipt', [POSController::class, 'receipt'])->name('orders.receipt');
        Route::post('/orders/{order}/accept', [POSController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{order}/reject', [POSController::class, 'rejectOrder'])->name('orders.reject');
        Route::post('/voucher/validate', [POSController::class, 'validateVoucher'])->name('voucher.validate');
    });

// ── Public QR ordering (Fase 3) ──────────────────────────────────────────────
Route::get('/order/{token}', [QrOrderController::class, 'show'])->name('order.show');
Route::post('/order/{token}/submit', [QrOrderController::class, 'submit'])->name('order.submit')->middleware('throttle:20,1');
Route::get('/order/{token}/history', [QrOrderController::class, 'history'])->name('order.history');
Route::get('/order/{token}/payment-status/{orderId}', [QrOrderController::class, 'paymentStatus'])->name('order.payment-status');
Route::post('/order/{token}/upload-proof/{orderId}', [QrOrderController::class, 'uploadProof'])->name('order.upload-proof');
Route::post('/order/{token}/simulate-xendit/{orderId}', [QrOrderController::class, 'simulateXenditPayment'])->name('order.simulate-xendit');
Route::get('/order-submitted', fn () => view('order.submitted'))->name('order.submitted');

// ── Midtrans webhook ──────────────────────────────────────────────────────────
Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhook.midtrans');
Route::post('/webhook/xendit', [XenditWebhookController::class, 'handle'])->name('webhook.xendit');

// ── Kitchen display ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant.active', 'branch.selected'])
    ->prefix('kitchen')
    ->name('kitchen.')
    ->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
        Route::get('/orders', [KitchenController::class, 'orders'])->name('orders');
        Route::post('/orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('orders.status');
    });

// ── API sync (PWA offline) ────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant.active', 'branch.selected'])
    ->prefix('api/sync')
    ->name('api.sync.')
    ->group(function () {
        Route::post('/orders', [SyncOrderController::class, 'store'])->name('orders');
    });

// ── Subscription ──────────────────────────────────────────────────────────────
Route::middleware('auth')->get('/subscription/expired', fn () => view('subscription.expired'))
    ->name('subscription.expired');

Route::middleware('auth')->prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
    Route::get('/callback', [BillingController::class, 'callback'])->name('callback');
});
