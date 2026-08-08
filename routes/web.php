<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\ApiLogController;

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

        Route::view('/dashboard','admin.dashboard.index')
            ->name('dashboard');

        Route::view('/products','admin.products.index')
            ->name('products');

        Route::get('/categories',[CategoryController::class,'index'])
            ->name('categories');

        Route::get('/sizes', [SizeController::class, 'index'])
            ->name('sizes');

        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions');

            Route::get('/transactions/create', [TransactionController::class, 'create'])
                ->name('transactions.create');
            
            Route::get('/transactions/{invoice}/print', function ($invoice) {
                    return view('admin.transactions.print', compact('invoice'));
                })->name('transactions.print');

        Route::get('/sales-reports', [SalesReportController::class, 'index'])
            ->name('sales-reports');

            Route::get('/sales-reports/print', [SalesReportController::class, 'print'])
                ->name('sales-reports.print');

        Route::get('/api-logs', [ApiLogController::class, 'index'])
            ->name('api-logs');
});