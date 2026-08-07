<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SizeController extends Controller
{
    public function index()
    {
        return view('admin.sizes.index');
    }
}