<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cart = collect($request->session()->get('cart', []));

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = $cart->sum(function ($item) {
            return (float) $item['price'] * (int) $item['qty'];
        });

        $totalItems = $cart->sum('qty');

        $shippingMethods = [
            [
                'value' => 'courier',
                'name' => 'Kurir',
                'description' => 'Pengiriman ke alamat yang Anda masukkan.',
            ],
        ];

        $paymentMethods = [
            [
                'value' => 'QRIS',
                'name' => 'QRIS',
                'description' => 'Bayar menggunakan QRIS.',
            ],
            [
                'value' => 'TRANSFER',
                'name' => 'Transfer Bank',
                'description' => 'Transfer ke rekening Eazywear.',
            ],
        ];

        return view('checkout.index', compact(
            'cart',
            'subtotal',
            'totalItems',
            'shippingMethods',
            'paymentMethods'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'email' => [
                'required',
                'email',
                'max:150',
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
            'shipping_address' => [
                'required',
                'string',
                'max:1000',
            ],
            'shipping_district' => [
                'required',
                'string',
                'max:100',
            ],
            'shipping_city' => [
                'required',
                'string',
                'max:100',
            ],
            'shipping_province' => [
                'required',
                'string',
                'max:100',
            ],
            'shipping_postal_code' => [
                'required',
                'string',
                'max:10',
            ],
            'shipping_method' => [
                'required',
                'string',
                Rule::in([
                    'courier',
                ]),
            ],
            'payment_method' => [
                'required',
                'string',
                Rule::in([
                    'QRIS',
                    'TRANSFER',
                ]),
            ],
        ]);

        $cart = collect($request->session()->get('cart', []));

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        try {
            $transaction = DB::transaction(function () use ($validated, $cart) {
                /*
                |--------------------------------------------------------------------------
                | 1. Customer
                |--------------------------------------------------------------------------
                */
                $customer = Customer::query()
                    ->where(function ($query) use ($validated) {
                        $query->where('phone', $validated['phone'])
                            ->orWhere('email', $validated['email']);
                    })
                    ->first();

                if (!$customer) {
                    $customer = Customer::create([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 2. Lock & validate cart items
                |--------------------------------------------------------------------------
                */
                $items = [];
                $subtotal = 0;

                foreach ($cart as $cartItem) {
                    $variantId = (int) ($cartItem['variant_id'] ?? 0);
                    $qty = (int) ($cartItem['qty'] ?? 0);
                    $customName = trim((string) ($cartItem['custom_name'] ?? ''));

                    if ($variantId <= 0 || $qty <= 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Data keranjang tidak valid.',
                        ]);
                    }

                    if ($customName === '') {
                        throw ValidationException::withMessages([
                            'cart' => 'Nama jersey belum diisi untuk salah satu produk.',
                        ]);
                    }

                    if (mb_strlen($customName) > 20) {
                        throw ValidationException::withMessages([
                            'cart' => 'Nama jersey maksimal 20 karakter.',
                        ]);
                    }

                    if (!preg_match('/^[\pL\s]+$/u', $customName)) {
                        throw ValidationException::withMessages([
                            'cart' => 'Nama jersey hanya boleh berisi huruf dan spasi.',
                        ]);
                    }

                    $variant = ProductVariant::with([
                        'product',
                        'size',
                        'color',
                    ])
                        ->lockForUpdate()
                        ->find($variantId);

                    if (!$variant) {
                        throw ValidationException::withMessages([
                            'cart' => 'Salah satu produk sudah tidak tersedia.',
                        ]);
                    }

                    if (!$variant->product || !$variant->product->status) {
                        throw ValidationException::withMessages([
                            'cart' => "Produk {$variant->product?->name} sudah tidak aktif.",
                        ]);
                    }

                    $inventory = Inventory::query()
                        ->where('product_variant_id', $variant->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw ValidationException::withMessages([
                            'cart' => "Stok untuk {$variant->sku} tidak ditemukan.",
                        ]);
                    }

                    $stock = (int) $inventory->stock;

                    if ($stock < $qty) {
                        throw ValidationException::withMessages([
                            'cart' => "Stok {$variant->product->name} tidak mencukupi. Stok tersedia: {$stock}.",
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Gunakan harga dari database, bukan harga dari session.
                    |--------------------------------------------------------------------------
                    */
                    $price = (float) $variant->price;
                    $itemSubtotal = $price * $qty;

                    $subtotal += $itemSubtotal;

                    $items[] = [
                        'variant' => $variant,
                        'inventory' => $inventory,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                        'custom_name' => trim($cartItem['custom_name'] ?? ''),
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | 3. Shipping
                |--------------------------------------------------------------------------
                | Untuk Sprint 2B ongkir masih 0.
                | Perhitungan ongkir akan kita kerjakan nanti.
                |--------------------------------------------------------------------------
                */
                $discount = 0;
                $shipping = 0;

                $total = $subtotal - $discount + $shipping;

                /*
                |--------------------------------------------------------------------------
                | 4. Generate Invoice
                |--------------------------------------------------------------------------
                */
                $invoiceNumber = $this->generateInvoiceNumber();

                /*
                |--------------------------------------------------------------------------
                | 5. Create Transaction
                |--------------------------------------------------------------------------
                */
                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'invoice_number' => $invoiceNumber,
                    'transaction_date' => now(),
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'shipping' => $shipping,
                    'total' => $total,
                    'status' => 'PENDING',
                    'source' => 'Website',
                    'shipping_name' => $validated['name'],
                    'shipping_email' => $validated['email'],
                    'shipping_phone' => $validated['phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_district' => $validated['shipping_district'],
                    'shipping_city' => $validated['shipping_city'],
                    'shipping_province' => $validated['shipping_province'],
                    'shipping_postal_code' => $validated['shipping_postal_code'],
                    'shipping_method' => $validated['shipping_method'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | 6. Create Transaction Items + decrease stock
                |--------------------------------------------------------------------------
                */
                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_variant_id' => $item['variant']->id,
                        'custom_name' => $item['custom_name'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $item['inventory']->decrement(
                        'stock',
                        $item['qty']
                    );
                }

                return $transaction;
            });

            /*
            |--------------------------------------------------------------------------
            | 7. Save invoice in session so success page belongs to this checkout
            |--------------------------------------------------------------------------
            */
            $request->session()->put(
                'checkout_success_invoice',
                $transaction->invoice_number
            );

            /*
            |--------------------------------------------------------------------------
            | 8. Clear cart after successful transaction
            |--------------------------------------------------------------------------
            */
            $request->session()->forget('cart');

            return redirect()
                ->route('checkout.success')
                ->with('success', 'Pesanan berhasil dibuat.');

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pesanan gagal dibuat. Silakan coba lagi.'
                );
        }
    }

    public function success(Request $request): View|RedirectResponse
    {
        $invoice = $request->session()->pull('checkout_success_invoice');

        if (!$invoice) {
            return redirect()->route('home');
        }

        $transaction = Transaction::with([
            'customer',
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ])
            ->where('invoice_number', $invoice)
            ->first();

        if (!$transaction) {
            return redirect()
                ->route('home')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        return view('checkout.success', compact('transaction'));
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(6));
        } while (
            Transaction::where('invoice_number', $invoice)->exists()
        );

        return $invoice;
    }
}