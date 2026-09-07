<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = collect($request->session()->get('cart', []));
        $subtotal = $cart->sum(function ($item) {
            return $item['price'] * $item['qty'];
        });
        $totalItems = $cart->sum('qty');
        return view('cart.index', compact('cart', 'subtotal', 'totalItems'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'custom_name' => ['required', 'string', 'max:20', 'regex:/^[\pL\s]+$/u'],
        ], [
            'custom_name.required' => 'Nama jersey wajib diisi.',
            'custom_name.max' => 'Nama jersey maksimal 20 karakter.',
            'custom_name.regex' => 'Nama jersey hanya boleh berisi huruf dan spasi.',
        ]);

        $customName = trim($validated['custom_name']);

        if ($customName === '') {
            return back()->with('error', 'Nama jersey wajib diisi.');
        }

        $variant = ProductVariant::with([
            'product',
            'size',
            'color',
            'inventory',
            'product.images',
        ])->findOrFail($validated['variant_id']);

        abort_unless($variant->product?->status, 404);

        $stock = (int) ($variant->inventory?->stock ?? 0);

        if ($stock <= 0) {
            return back()->with('error', 'Produk sedang tidak tersedia.');
        }

        $cart = $request->session()->get('cart', []);
        $customName = trim($validated['custom_name']);
        $cartKey = $variant->id . '-' . sha1(mb_strtolower($customName));

        $currentQty = $cart[$cartKey]['qty'] ?? 0;
        $newQty = $currentQty + (int) $validated['qty'];

        if ($newQty > $stock) {
            return back()->with(
                'error',
                'Jumlah pembelian melebihi stok yang tersedia.'
            );
        }

        $thumbnail = $variant->product->images
            ->where('is_thumbnail', true)
            ->sortBy('sort_order')
            ->first();

        $image = $thumbnail
            ? asset('storage/' . $thumbnail->image)
            : asset('images/products/placeholder.png');

        $cart[$cartKey] = [
            'cart_key' => $cartKey,
            'variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_name' => $variant->product->name,
            'size_id' => $variant->size_id,
            'size_name' => $variant->size?->name ?? '-',
            'color_id' => $variant->color_id,
            'color_name' => $variant->color?->name ?? null,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'qty' => $validated['qty'],
            'stock' => $stock,
            'image' => $image,
            'custom_name' => $customName,
        ];

        $request->session()->put('cart', $cart);

        return back()->with(
            'success',
            $variant->product->name . ' berhasil ditambahkan ke keranjang.'
        );
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);
        $cart = $request->session()->get('cart', []);
        if (!isset($cart[$key])) {
            return back()->with('error', 'Item keranjang tidak ditemukan.');
        }
        $stock = (int) ($cart[$key]['stock'] ?? 0);
        if ($validated['qty'] > $stock) {
            return back()->with('error', 'Jumlah produk melebihi stok tersedia.');
        }
        $cart[$key]['qty'] = $validated['qty'];
        $request->session()->put('cart', $cart);
        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function remove(Request $request, string $key): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        if (isset($cart[$key])) {
            unset($cart[$key]);
            $request->session()->put('cart', $cart);
        }
        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return redirect()
            ->route('cart.index')
            ->with('success', 'Keranjang berhasil dikosongkan.');
    }
}