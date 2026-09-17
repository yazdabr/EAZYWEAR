<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\ApiLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\ProductionReportController;

/*
|--------------------------------------------------------------------------
| Website
|--------------------------------------------------------------------------
*/

Route::view('/', 'pages.home')->name('home');

Route::get('/catalog', [ProductController::class, 'catalog'])
    ->name('catalog');

Route::get('/catalog/product/{product:slug}', [ProductController::class, 'productDetail'])
    ->name('product.detail');

Route::get('/cart', [CartController::class, 'index'])
    ->name('cart.index');

Route::post('/cart/add', [CartController::class, 'add'])
    ->name('cart.add');

Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');

Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');

Route::delete('/cart', [CartController::class, 'clear'])
    ->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'index'])
    ->name('checkout.index');

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');

Route::get('/checkout/success', [CheckoutController::class, 'success'])
    ->name('checkout.success');

Route::view('/about', 'pages.about')
    ->name('about');

Route::view('/contact', 'pages.contact')
    ->name('contact');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;

        if ($role === 'production') {
            return redirect()->route('admin.production-reports');
        }

        if ($role === 'management') {
            return redirect()->route('admin.transactions');
        }

        if ($role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Super Admin
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:super_admin')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])
                ->name('dashboard');
        });

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:super_admin')->group(function () {

            // Products
            Route::get('/products/search', [ProductController::class, 'search'])
                ->name('products.search');

            Route::resource('products', ProductController::class)
                ->names([
                    'index' => 'products',
                    'create' => 'products.create',
                    'store' => 'products.store',
                    'show' => 'products.show',
                    'edit' => 'products.edit',
                    'update' => 'products.update',
                    'destroy' => 'products.destroy',
                ]);

            // Categories
            Route::get('/categories/search', [CategoryController::class, 'search'])
                ->name('categories.search');

            Route::resource('categories', CategoryController::class)
                ->names([
                    'index' => 'categories',
                    'create' => 'categories.create',
                    'store' => 'categories.store',
                    'show' => 'categories.show',
                    'edit' => 'categories.edit',
                    'update' => 'categories.update',
                    'destroy' => 'categories.destroy',
                ]);

            // Sizes
            Route::resource('sizes', SizeController::class)
                ->names([
                    'index' => 'sizes',
                    'create' => 'sizes.create',
                    'store' => 'sizes.store',
                    'show' => 'sizes.show',
                    'edit' => 'sizes.edit',
                    'update' => 'sizes.update',
                    'destroy' => 'sizes.destroy',
                ]);

            // Transaction Management
            Route::get('/transactions/create', [TransactionController::class, 'create'])
                ->name('transactions.create');

            Route::get('/transactions/customer-search', [TransactionController::class, 'customerSearch'])
                ->name('transactions.customer-search');

            Route::post('/transactions', [TransactionController::class, 'store'])
                ->name('transactions.store');

            Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])
                ->name('transactions.update-status');

            Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])
                ->name('transactions.cancel');

            Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
                ->name('transactions.destroy');

            Route::get('/transactions/{invoice}/print', [TransactionController::class, 'print'])
                ->name('transactions.print');

            // API Logs
            Route::get('/api-logs', [ApiLogController::class, 'index'])
                ->name('api-logs');
        });

        /*
        |--------------------------------------------------------------------------
        | Transactions & Reports
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:super_admin,management')->group(function () {

            Route::get('/transactions', [TransactionController::class, 'index'])
                ->name('transactions');

            Route::get('/sales-reports', [SalesReportController::class, 'index'])
                ->name('sales-reports');

            Route::get('/sales-reports/print', [SalesReportController::class, 'print'])
                ->name('sales-reports.print');

            Route::get('/sales-reports/export', [SalesReportController::class, 'export'])
                ->name('sales-reports.export');
        });



        /*
        |--------------------------------------------------------------------------
        | Produksi
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:super_admin,management,production')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Index Produksi
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/productions',
                    [ProductionController::class, 'index']
                )->name('productions');


                /*
                |--------------------------------------------------------------------------
                | Tambah Produksi
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/productions/create',
                    [ProductionController::class, 'create']
                )->name('productions.create');

                Route::post(
                    '/productions',
                    [ProductionController::class, 'store']
                )->name('productions.store');


                /*
                |--------------------------------------------------------------------------
                | Edit Produksi
                |--------------------------------------------------------------------------
                | Diletakkan sebelum route {production}
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/productions/{production}/edit',
                    [ProductionController::class, 'edit']
                )->name('productions.edit');

                Route::put(
                    '/productions/{production}',
                    [ProductionController::class, 'update']
                )->name('productions.update');


                /*
                |--------------------------------------------------------------------------
                | Hapus Produksi
                |--------------------------------------------------------------------------
                */

                Route::delete(
                    '/productions/{production}',
                    [ProductionController::class, 'destroy']
                )->name('productions.destroy');


                /*
                |--------------------------------------------------------------------------
                | Detail Produksi
                |--------------------------------------------------------------------------
                | Diletakkan setelah route edit, update, dan destroy
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/productions/{production}',
                    [ProductionController::class, 'show']
                )->name('productions.show');

            });

        
        /*
        |--------------------------------------------------------------------------
        | Production Reports
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:super_admin,management,production')
            ->group(function () {

                Route::get(
                    '/production-reports',
                    [ProductionReportController::class, 'index']
                )->name('production-reports');

                Route::get(
                    '/production-reports/print',
                    [ProductionReportController::class, 'print']
                )->name('production-reports.print');

                Route::get(
                    '/production-reports/export',
                    [ProductionReportController::class, 'export']
                )->name('production-reports.export');

            });
    });