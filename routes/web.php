<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\ApiLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Website
|--------------------------------------------------------------------------
*/

Route::view('/', 'pages.home')->name('home');

Route::view('/catalog', 'pages.catalog')->name('catalog');

Route::view('/catalog/product', 'pages.product-detail')
    ->name('product.detail');

Route::view('/about', 'pages.about')
    ->name('about');

Route::view('/contact', 'pages.contact')
    ->name('contact');


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        // dashboard
        Route::view('/dashboard','admin.dashboard.index')
            ->name('dashboard');
        // products
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
        // categoris

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

        // sizes
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
            
        // transactions
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions');

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

        Route::get('/transactions/{invoice}/print', function ($invoice) {
            return view('admin.transactions.print', compact('invoice'));
        })->name('transactions.print');

        // sales reports
        Route::get('/sales-reports', [SalesReportController::class, 'index'])
            ->name('sales-reports');

            Route::get('/sales-reports/print', [SalesReportController::class, 'print'])
                ->name('sales-reports.print');
        // api logs
        Route::get('/api-logs', [ApiLogController::class, 'index'])
            ->name('api-logs');
});