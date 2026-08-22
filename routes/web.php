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

/*
|--------------------------------------------------------------------------
| Website
|--------------------------------------------------------------------------
*/

Route::view('/', 'pages.home')->name('home');
Route::get('/catalog', [ProductController::class, 'catalog'])
    ->name('catalog');
Route::get('/catalog/product/{product}', [ProductController::class, 'productDetail'])
    ->name('product.detail');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'management') {
            return redirect()->route('admin.transactions');
        }

        return redirect()->route('admin.dashboard');
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
        });
    });