<?php

use App\Http\Controllers\Admin\ApiLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\ProductionReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\OfflineSalesReportController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\BiteshipWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');

// URL baru untuk SHOP
Route::get('/shop', [ProductController::class, 'shop'])->name('shop');
Route::get('/shop/product/{product:slug}', [ProductController::class, 'productDetail'])
    ->name('product.detail');

// Redirect URL lama ke URL baru
Route::get('/catalog', function () {
    $query = request()->getQueryString();

    return redirect('/shop' . ($query ? '?' . $query : ''), 301);
});

Route::get('/catalog/product/{slug}', function (string $slug) {
    $query = request()->getQueryString();

    return redirect('/shop/product/' . $slug . ($query ? '?' . $query : ''), 301);
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

Route::post('/checkout/shipping-rates', [CheckoutController::class, 'shippingRates'])
    ->middleware('throttle:20,1')
    ->name('checkout.shipping-rates');

Route::get('/checkout/fulfillment-availability', [CheckoutController::class, 'fulfillmentAvailability'])
    ->middleware('throttle:30,1')
    ->name('checkout.fulfillment-availability');

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('checkout.store');

Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/checkout/payment-status', [CheckoutController::class, 'paymentStatus'])
    ->middleware('throttle:60,1')
    ->name('checkout.payment-status');

Route::get('/cek-pesanan', [OrderTrackingController::class, 'index'])
    ->name('orders.tracking');

Route::post('/cek-pesanan', [OrderTrackingController::class, 'search'])
    ->middleware('throttle:5,1')
    ->name('orders.tracking.search');

Route::get('/cek-pesanan/{invoice}/shipment/{shipment}/tracking', [
    OrderTrackingController::class,
    'trackingShipment',
])
    ->middleware('throttle:30,1')
    ->name('orders.tracking.shipment');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::post('/webhooks/biteship', [BiteshipWebhookController::class, 'handle'])
    ->name('webhooks.biteship');

Route::get('/login', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;

        if (in_array($role, ['production', 'reports'], true)) {

            return redirect()->route('admin.production-reports');

        }

        if ($role === 'management') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])->middleware('guest')->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::resource('products', ProductController::class)->names([
            'index' => 'products',
            'create' => 'products.create',
            'store' => 'products.store',
            'show' => 'products.show',
            'edit' => 'products.edit',
            'update' => 'products.update',
            'destroy' => 'products.destroy',
        ]);
        Route::get('/categories/search', [CategoryController::class, 'search'])->name('categories.search');
        Route::resource('categories', CategoryController::class)->names([
            'index' => 'categories',
            'create' => 'categories.create',
            'store' => 'categories.store',
            'show' => 'categories.show',
            'edit' => 'categories.edit',
            'update' => 'categories.update',
            'destroy' => 'categories.destroy',
        ]);
        Route::resource('sizes', SizeController::class)->names([
            'index' => 'sizes',
            'create' => 'sizes.create',
            'store' => 'sizes.store',
            'show' => 'sizes.show',
            'edit' => 'sizes.edit',
            'update' => 'sizes.update',
            'destroy' => 'sizes.destroy',
        ]);
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::get('/transactions/customer-search', [TransactionController::class, 'customerSearch'])->name('transactions.customer-search');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::post(
            '/transactions/{transaction}/check-payment',
            [TransactionController::class, 'checkPayment']
        )
        ->name('transactions.check-payment');
        Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');


        Route::patch('/transactions/{transaction}/fulfillments/{fulfillment}/ship', [TransactionController::class, 'shipFulfillment'])->name('transactions.fulfillments.ship');
        Route::patch('/transactions/{transaction}/fulfillments/{fulfillment}/shipping-address', [TransactionController::class, 'updateFulfillmentShippingAddress'])->name('transactions.fulfillments.shipping-address.update');
        Route::patch('/transactions/{transaction}/fulfillments/{fulfillment}/complete', [TransactionController::class, 'completeFulfillment'])->name('transactions.fulfillments.complete');
        Route::get('/transactions/{transaction}/fulfillments/{fulfillment}/tracking', [TransactionController::class, 'trackingFulfillment'])->name('transactions.fulfillments.tracking');
        Route::get('/transactions/{transaction}/fulfillments/{fulfillment}/label', [TransactionController::class, 'printFulfillmentLabel'])->name('transactions.fulfillments.label');

        Route::patch(
            '/transactions/{transaction}/shipments/{shipment}/ship',
            [TransactionController::class, 'shipShipment']
        )->name('transactions.shipments.ship');

        Route::get(
            '/transactions/{transaction}/tracking',
            [TransactionController::class, 'trackingLegacyTransaction']
        )->name('transactions.legacy-tracking');
        Route::get(
            '/transactions/{transaction}/shipments/{shipment}/tracking',
            [TransactionController::class, 'trackingShipment']
        )->name('transactions.shipments.tracking');
        Route::get(
            '/transactions/{transaction}/shipments/{shipment}/label',
            [TransactionController::class, 'printShipmentLabel']
        )->name('transactions.shipments.label');

        Route::get(
            '/transactions/{transaction}/label',
            [TransactionController::class, 'printLegacyTransactionLabel']
        )->name('transactions.legacy-label');

        Route::patch(
            '/transactions/{transaction}/shipments/{shipment}/shipping-address',
            [TransactionController::class, 'updateShipmentShippingAddress']
        )->name('transactions.shipments.shipping-address.update');
        Route::post('/transactions/process-all', [
            TransactionController::class,
            'processAllPaid',
        ])->name('transactions.process-all');

        Route::patch('/transactions/{transaction}/process', [TransactionController::class, 'process'])
            ->name('transactions.process');
        Route::patch('/transactions/{transaction}/ship', [TransactionController::class, 'ship'])
            ->name('transactions.ship');
        Route::patch(
            '/transactions/{transaction}/shipping-address',
            [TransactionController::class, 'updateShippingAddress']
        )->name('transactions.shipping-address.update');
        Route::patch(
            '/transactions/{transaction}/customer-contact',
            [TransactionController::class, 'updateCustomerContact']
        )->name('transactions.customer-contact.update');
        Route::patch(
                '/transactions/{transaction}/complete',
                [TransactionController::class, 'complete']
            )->name('transactions.complete');
        Route::patch(
            '/transactions/{transaction}/shipping',
            [TransactionController::class, 'updateShipping']
        )->name('transactions.shipping.update');
        Route::get('/api-logs', [ApiLogController::class, 'index'])->name('api-logs');
    });

    
    // Kasir Booth dan transaksi offline
    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/kasir', [
            \App\Http\Controllers\Admin\BoothPosController::class,
            'index',
        ])->name('kasir');

        Route::post('/kasir/checkout', [
            \App\Http\Controllers\Admin\BoothPosController::class,
            'store',
        ])->name('kasir.checkout');

        Route::post('/kasir/update-stock', [
            \App\Http\Controllers\Admin\BoothPosController::class,
            'updateStock',
        ])->name('kasir.update-stock');

        Route::get('/kasir/payment-status/{invoice}', [
            \App\Http\Controllers\Admin\BoothPosController::class,
            'paymentStatus',
        ])->name('kasir.payment-status');

        Route::get('/whatsapp', [
            \App\Http\Controllers\Admin\OfflineTransactionController::class,
            'index',
        ])->name('offline-transactions.index');

        Route::get('/whatsapp/{transaction}', [
            \App\Http\Controllers\Admin\OfflineTransactionController::class,
            'show',
        ])->name('offline-transactions.show');
    });


    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/transactions/payment-alerts', [TransactionController::class, 'paymentAlerts'])->name('transactions.payment-alerts');
        Route::get('/transactions/payment-sound-preference', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'enabled' => (bool) $request->user()->payment_sound_enabled,
            ])->header('Cache-Control', 'no-store, private');
        })->name('transactions.payment-sound-preference');
        Route::post('/transactions/payment-sound-preference', [TransactionController::class, 'updatePaymentSoundPreference'])->name('transactions.payment-sound-preference');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('/transactions/production-export', [TransactionController::class, 'exportProductionOrders'])->name('transactions.production-export');
        Route::get('/transactions/{invoice}/print', [TransactionController::class, 'print'])->name('transactions.print');
        Route::get('/transactions/{invoice}/excel', [TransactionController::class, 'exportInvoiceExcel'])->name('transactions.excel');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->middleware('role:super_admin,management')->name('transactions.show');
        Route::put('/transactions/hidden-statuses', [TransactionController::class, 'updateHiddenStatuses'])->name('transactions.hidden-statuses.update');
    });

    Route::middleware('role:super_admin,management,production,reports')->group(function () {
        Route::get('/sales-reports', [SalesReportController::class, 'index'])->name('sales-reports');
        Route::get('/sales-reports/print', [SalesReportController::class, 'print'])->name('sales-reports.print');
        Route::get('/sales-reports/export', [SalesReportController::class, 'export'])->name('sales-reports.export');

        Route::get('/whatsapp-reports', [OfflineSalesReportController::class, 'index'])->name('offline-sales-reports');
        Route::get('/whatsapp-reports/print', [OfflineSalesReportController::class, 'print'])->name('offline-sales-reports.print');
        Route::get('/whatsapp-reports/export', [OfflineSalesReportController::class, 'export'])->name('offline-sales-reports.export');
    });
    Route::middleware('role:super_admin,management,production')->group(function () {
        Route::get('/productions', [ProductionController::class, 'index'])->name('productions');
        Route::get('/productions/create', [ProductionController::class, 'create'])->name('productions.create');
        Route::post('/productions', [ProductionController::class, 'store'])->name('productions.store');
        Route::get('/productions/{production}/edit', [ProductionController::class, 'edit'])->name('productions.edit');
        Route::put('/productions/{production}', [ProductionController::class, 'update'])->name('productions.update');
        Route::delete('/productions/{production}', [ProductionController::class, 'destroy'])->name('productions.destroy');
        Route::get('/productions/{production}', [ProductionController::class, 'show'])->name('productions.show');
    });

    Route::middleware('role:super_admin,management,production,reports')->group(function () {
        Route::get('/production-reports', [ProductionReportController::class, 'index'])->name('production-reports');
        Route::get('/production-reports/print', [ProductionReportController::class, 'print'])->name('production-reports.print');
        Route::get('/production-reports/export', [ProductionReportController::class, 'export'])->name('production-reports.export');
    });

    // DATA TRANSAKSI NNS
    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/nns-transactions', function () {
            return view('admin.nns-transactions.index');
        })->name('nns-transactions.index');

        Route::get('/nns-transactions/detail', function () {
            return view('admin.nns-transactions.show');
        })->name('nns-transactions.show');

        Route::get('/nns-sales-reports', function () {
            return view('admin.nns-sales-reports.index');
        })->name('nns-sales-reports');

        // Laporan Penjualan NNS
        Route::get('/nns-sales-reports', function () {
            return view('admin.nns-sales-reports.index', [
                'years' => [2026, 2025],
                'totalTransactions' => 0,
                'transactionGrowth' => 0,
                'totalProductsSold' => 0,
                'productGrowth' => 0,
                'topProducts' => collect(),
                'salesCategories' => collect(),
                'transactions' => collect(),
                'paginatedTransactions' => new \Illuminate\Pagination\LengthAwarePaginator(
                    [],
                    0,
                    10,
                    1,
                    [
                        'path' => request()->url(),
                        'query' => request()->query(),
                    ]
                ),
            ]);
        })->name('nns-sales-reports');

        // Print Laporan NNS (sementara)
        Route::get('/nns-sales-reports/print', function () {
            return redirect()->route('admin.nns-sales-reports');
        })->name('nns-sales-reports.print');
    });

    // TRANSAKSI STADION
    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('/stadium-transactions', [
            \App\Http\Controllers\Admin\StadiumTransactionController::class,
            'index',
        ])->name('stadium-transactions.index');

        Route::get('/stadium-transactions/{transaction}', [
            \App\Http\Controllers\Admin\StadiumTransactionController::class,
            'show',
        ])->name('stadium-transactions.show');
    });

    // LAPORAN PENJUALAN STADION
    Route::middleware('role:super_admin,management,production,reports')->group(function () {
        Route::get('/stadium-sales-reports', [
            \App\Http\Controllers\Admin\StadiumSalesReportController::class,
            'index',
        ])->name('stadium-sales-reports');

        Route::get('/stadium-sales-reports/print', [
            \App\Http\Controllers\Admin\StadiumSalesReportController::class,
            'print',
        ])->name('stadium-sales-reports.print');
    });

});
