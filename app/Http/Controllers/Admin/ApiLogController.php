<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ApiLogController extends Controller
{
    public function index()
    {
        return view('admin.api-logs.index');
    }
}