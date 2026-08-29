<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'updated_at']);

        return response()
            ->view('sitemap.xml', compact('products'))
            ->header('Content-Type', 'application/xml');
    }
}