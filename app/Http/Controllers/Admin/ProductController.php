<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with([
            'category',
            'images',
            'variants.inventory',
        ]);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        $products = $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $lastProductCode = Product::query()
            ->where('product_code', 'like', 'PRD-%')
            ->orderByDesc('id')
            ->value('product_code');

        if ($lastProductCode && preg_match('/PRD-(\d+)/', $lastProductCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        $nextProductCode = 'PRD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'nextProductCode' => $nextProductCode,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim($request->input('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $products = Product::query()
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(8)
            ->get([
                'id',
                'name',
                'product_code',
            ]);

        return response()->json($products);
    }

    public function create(): View
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:50', 'unique:products,product_code'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'material' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'product_code.required' => 'Kode produk wajib diisi.',
            'product_code.unique' => 'Kode produk sudah digunakan.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'status.required' => 'Status produk wajib dipilih.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran foto terlalu besar. Maksimal upload adalah 10 MB.',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $product = Product::create([
                'product_code' => $validated['product_code'],
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'material' => $validated['material'] ?? null,
                'status' => $validated['status'],
            ]);

            $size = Size::query()->first();
            $color = Color::query()->first();

            if (!$size) {
                throw new \Exception('Tabel sizes belum memiliki data.');
            }

            if (!$color) {
                throw new \Exception('Tabel colors belum memiliki data.');
            }

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'size_id' => $size->id,
                'color_id' => $color->id,
                'sku' => $validated['product_code'],
                'price' => $validated['price'],
            ]);

            Inventory::create([
                'product_variant_id' => $variant->id,
                'stock' => $validated['stock'],
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');

                $product->images()->create([
                    'image' => $imagePath,
                    'is_thumbnail' => true,
                    'sort_order' => 1,
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan.',
                    'data' => $product
                ], 200);
            }

            return redirect()
                ->route('admin.products')
                ->with('success', 'Produk berhasil ditambahkan.');
        });
    }

    public function show(Product $product): View
    {
        $product->load([
            'category',
            'images',
            'variants.size',
            'variants.color',
            'variants.inventory',
        ]);

        return view('admin.products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load([
            'category',
            'images',
            'variants.inventory',
        ]);

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:50', 'unique:products,product_code,' . $product->id],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'material' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'product_code.required' => 'Kode produk wajib diisi.',
            'product_code.unique' => 'Kode produk sudah digunakan.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh kurang dari 0.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok tidak boleh kurang dari 0.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 10 MB.',
            'status.required' => 'Status produk wajib dipilih.',
        ]);

        DB::transaction(function () use ($request, $validated, $product) {
            $product->update([
                'product_code' => $validated['product_code'],
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug($validated['name'], $product->id),
                'description' => $validated['description'] ?? null,
                'material' => $validated['material'] ?? null,
                'status' => $validated['status'],
            ]);

            $variant = $product->variants()->with('inventory')->first();

            if ($variant) {
                $variant->update([
                    'price' => $validated['price'],
                ]);

                if ($variant->inventory) {
                    $variant->inventory->update([
                        'stock' => $validated['stock'],
                    ]);
                } else {
                    $variant->inventory()->create([
                        'stock' => $validated['stock'],
                    ]);
                }
            }

            if ($request->hasFile('image')) {
                $oldThumbnail = $product->images()
                    ->where('is_thumbnail', true)
                    ->first();

                if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail->image)) {
                    Storage::disk('public')->delete($oldThumbnail->image);
                }

                $imagePath = $request->file('image')->store('products', 'public');

                if ($oldThumbnail) {
                    $oldThumbnail->update([
                        'image' => $imagePath,
                    ]);
                } else {
                    $product->images()->create([
                        'image' => $imagePath,
                        'is_thumbnail' => true,
                        'sort_order' => 1,
                    ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui.',
                'data' => $product
            ], 200);
        }

        return redirect()
            ->route('admin.products')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->variants()->whereHas('transactionItems')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($product) {
                $product->load([
                    'images',
                    'variants.inventory.stockMovements',
                ]);

                foreach ($product->images as $image) {
                    if ($image->image && Storage::disk('public')->exists($image->image)) {
                        Storage::disk('public')->delete($image->image);
                    }

                    $image->delete();
                }

                foreach ($product->variants as $variant) {
                    if ($variant->inventory) {
                        $variant->inventory->stockMovements()->delete();
                        $variant->inventory->delete();
                    }

                    $variant->delete();
                }

                $product->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus.',
                'id' => $product->id,
            ], 200);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Produk gagal dihapus. ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}