<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderTrackingController extends Controller
{
    public function index(): View
    {
        return view('orders.tracking-search');
    }


    public function search(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],
        ]);


        $transaction = Transaction::with([
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ])
        ->where('invoice_number', $validated['invoice_number'])
        ->where('shipping_email', $validated['email'])
        ->first();


        if (! $transaction) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pesanan tidak ditemukan. Pastikan nomor invoice dan email sudah benar.'
                );
        }


        return view(
            'orders.tracking-detail',
            compact('transaction')
        );
    }
}