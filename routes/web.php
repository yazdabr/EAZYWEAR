<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');

Route::view('/catalog', 'pages.catalog')->name('catalog');

Route::view('/catalog/product', 'pages.product-detail')
    ->name('product.detail');

Route::view('/about', 'pages.about')->name('about');

Route::view('/contact', 'pages.contact')->name('contact');
