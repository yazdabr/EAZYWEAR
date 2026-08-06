<?php

use Illuminate\Support\Facades\Route;

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

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::view('/dashboard', 'admin.dashboard.index')
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        Route::view('/products', 'admin.products.index')
            ->name('products.index');

        Route::view('/categories', 'admin.categories.index')
            ->name('categories.index');

        Route::view('/sizes', 'admin.sizes.index')
            ->name('sizes.index');

        Route::view('/colors', 'admin.colors.index')
            ->name('colors.index');

        Route::view('/product-images', 'admin.product-images.index')
            ->name('product-images.index');

        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        Route::view('/transactions', 'admin.transactions.index')
            ->name('transactions.index');

        Route::view('/api-logs', 'admin.api-logs.index')
            ->name('api-logs.index');

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::view('/reports', 'admin.reports.index')
            ->name('reports.index');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::view('/users', 'admin.users.index')
            ->name('users.index');

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::view('/profile', 'admin.profile.index')
            ->name('profile.index');

    });