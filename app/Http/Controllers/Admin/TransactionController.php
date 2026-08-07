<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function index()
    {
        return view('admin.transactions.index');
    }

    public function create()
    {
        return view('admin.transactions.create');
    }
}