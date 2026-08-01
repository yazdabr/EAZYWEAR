<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home');

Route::view('/catalog', 'catalog.index');

Route::view('/about', 'about.index');

Route::view('/contact', 'contact.index');
