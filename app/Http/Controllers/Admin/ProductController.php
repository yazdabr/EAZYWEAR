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
            'variants.size',
            'variants.color',
            'variants.inventory',
        ]);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $sizes = Size::query()
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

        $nextProductCode = 'PRD-' . str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'sizes' => $sizes,
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

        $sizes = Size::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.create', [
            'categories' => $categories,
            'sizes' => $sizes,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $sizeIds = collect($request->input('size_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Ambil hanya variant dari Size yang dipilih
        |--------------------------------------------------------------------------
        |
        | Form memang mengirim semua variants.
        | Tetapi yang kita proses hanya Size yang dipilih.
        |
        */
        $allVariants = $request->input('variants', []);

        $selectedVariants = collect($sizeIds)
            ->mapWithKeys(function ($sizeId) use ($allVariants) {
                $data = $allVariants[$sizeId]
                    ?? $allVariants[(string) $sizeId]
                    ?? [];

                return [
                    $sizeId => $data,
                ];
            })
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Buat data request khusus untuk validation
        |--------------------------------------------------------------------------
        */
        $request->merge([
            'size_ids' => $sizeIds,
            'variants' => $selectedVariants,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        try {
            $validated = $request->validate([
                'product_code' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:products,product_code',
                ],

                'category_id' => [
                    'required',
                    'exists:categories,id',
                ],

                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'material' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:10240',
                ],

                'size_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'size_ids.*' => [
                    'integer',
                    'exists:sizes,id',
                ],

                'variants' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'variants.*.price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'variants.*.stock' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ], [
                'product_code.required' => 'Kode produk wajib diisi.',
                'product_code.unique' => 'Kode produk sudah digunakan.',

                'category_id.required' => 'Kategori wajib dipilih.',
                'category_id.exists' => 'Kategori tidak ditemukan.',

                'name.required' => 'Nama produk wajib diisi.',
                'name.max' => 'Nama produk maksimal 150 karakter.',

                'status.required' => 'Status produk wajib dipilih.',

                'image.image' => 'File harus berupa gambar.',
                'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
                'image.max' => 'Ukuran foto terlalu besar. Maksimal upload adalah 10 MB.',

                'size_ids.required' => 'Minimal satu ukuran produk harus dipilih.',
                'size_ids.array' => 'Format ukuran tidak valid.',
                'size_ids.min' => 'Minimal satu ukuran produk harus dipilih.',
                'size_ids.*.exists' => 'Ukuran produk tidak ditemukan.',

                'variants.required' => 'Data ukuran produk wajib diisi.',
                'variants.array' => 'Format data ukuran tidak valid.',
                'variants.min' => 'Minimal satu ukuran produk harus memiliki harga dan stok.',

                'variants.*.price.required' => 'Harga ukuran wajib diisi.',
                'variants.*.price.numeric' => 'Harga ukuran harus berupa angka.',
                'variants.*.price.min' => 'Harga ukuran tidak boleh kurang dari 0.',

                'variants.*.stock.required' => 'Stok ukuran wajib diisi.',
                'variants.*.stock.integer' => 'Stok ukuran harus berupa angka bulat.',
                'variants.*.stock.min' => 'Stok ukuran tidak boleh kurang dari 0.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            /*
            |--------------------------------------------------------------------------
            | AJAX / JSON Response
            |--------------------------------------------------------------------------
            */
            if (
                $request->ajax() ||
                $request->expectsJson() ||
                $request->header('Accept') === 'application/json'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon periksa kembali data produk.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Product + Variant + Inventory
        |--------------------------------------------------------------------------
        */
        try {
            $result = DB::transaction(function () use (
                $request,
                $validated
            ) {
                /*
                |--------------------------------------------------------------------------
                | Product
                |--------------------------------------------------------------------------
                */
                $product = Product::create([
                    'product_code' => $validated['product_code'],
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'slug' => $this->generateUniqueSlug(
                        $validated['name']
                    ),
                    'description' => $validated['description'] ?? null,
                    'material' => $validated['material'] ?? null,
                    'status' => $validated['status'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Size
                |--------------------------------------------------------------------------
                */
                $sizeIds = collect($validated['size_ids'])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | Variant
                |--------------------------------------------------------------------------
                */
                $variantsData = $validated['variants'] ?? [];

                /*
                |--------------------------------------------------------------------------
                | Color Default
                |--------------------------------------------------------------------------
                */
                $color = Color::query()->first();

                if (!$color) {
                    throw new \Exception(
                        'Tabel colors belum memiliki data.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Ambil Size yang dipilih
                |--------------------------------------------------------------------------
                */
                $sizes = Size::query()
                    ->whereIn('id', $sizeIds)
                    ->orderBy('id')
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Buat Variant + Inventory
                |--------------------------------------------------------------------------
                */
                foreach ($sizes as $size) {

                    $sizeId = (string) $size->id;

                    $variantData = $variantsData[$sizeId]
                        ?? $variantsData[$size->id]
                        ?? null;

                    if (!$variantData) {
                        throw new \Exception(
                            "Data harga dan stok untuk ukuran {$size->name} belum lengkap."
                        );
                    }

                    $sku = $product->product_code . '-' .
                        strtoupper(Str::slug($size->name));

                    /*
                    |--------------------------------------------------------------------------
                    | Product Variant
                    |--------------------------------------------------------------------------
                    */
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'size_id' => $size->id,
                        'color_id' => $color->id,
                        'sku' => $sku,
                        'price' => $variantData['price'],
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Inventory
                    |--------------------------------------------------------------------------
                    */
                    Inventory::create([
                        'product_variant_id' => $variant->id,
                        'stock' => $variantData['stock'],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */
                if ($request->hasFile('image')) {

                    $imagePath = $request
                        ->file('image')
                        ->store('products', 'public');

                    $product->images()->create([
                        'image' => $imagePath,
                        'is_thumbnail' => true,
                        'sort_order' => 1,
                    ]);
                }

                return $product;
            });

            /*
            |--------------------------------------------------------------------------
            | JSON Response untuk AJAX
            |--------------------------------------------------------------------------
            */
            if (
                $request->ajax() ||
                $request->expectsJson() ||
                $request->header('Accept') === 'application/json'
            ) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan.',
                    'data' => $result->load([
                        'variants.size',
                        'variants.color',
                        'variants.inventory',
                    ]),
                ], 201);
            }

            /*
            |--------------------------------------------------------------------------
            | Normal Request
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route('admin.products')
                ->with(
                    'success',
                    'Produk berhasil ditambahkan.'
                );

        } catch (\Throwable $e) {

            report($e);

            /*
            |--------------------------------------------------------------------------
            | JSON Error
            |--------------------------------------------------------------------------
            */
            if (
                $request->ajax() ||
                $request->expectsJson() ||
                $request->header('Accept') === 'application/json'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }
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
            'variants.size',
            'variants.color',
            'variants.inventory',
        ]);

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $sizes = Size::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'sizes' => $sizes,
        ]);
    }

    public function update(
        Request $request,
        Product $product
    ): JsonResponse|RedirectResponse {

            $sizeIds = collect($request->input('size_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $allVariants = $request->input('variants', []);

        $selectedVariants = collect($sizeIds)
            ->mapWithKeys(function ($sizeId) use ($allVariants) {
                $data = $allVariants[$sizeId]
                    ?? $allVariants[(string) $sizeId]
                    ?? [];

                return [
                    $sizeId => $data,
                ];
            })
            ->all();

        $request->merge([
            'size_ids' => $sizeIds,
            'variants' => $selectedVariants,
        ]);
        $validated = $request->validate([
            'product_code' => [
                'required',
                'string',
                'max:50',
                'unique:products,product_code,' . $product->id,
            ],

            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'material' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'size_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'size_ids.*' => [
                'integer',
                'exists:sizes,id',
            ],

            'variants' => [
                'required',
                'array',
                'min:1',
            ],

            'variants.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'variants.*.stock' => [
                'required',
                'integer',
                'min:0',
            ],
        ], [
            'product_code.required' => 'Kode produk wajib diisi.',
            'product_code.unique' => 'Kode produk sudah digunakan.',

            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',

            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',

            'status.required' => 'Status produk wajib dipilih.',

            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 10 MB.',

            'size_ids.required' => 'Minimal satu ukuran produk harus dipilih.',
            'size_ids.min' => 'Minimal satu ukuran produk harus dipilih.',
            'size_ids.*.exists' => 'Ukuran produk tidak ditemukan.',

            'variants.required' => 'Data ukuran produk wajib diisi.',
            'variants.array' => 'Format data ukuran tidak valid.',
            'variants.min' => 'Minimal satu ukuran produk harus memiliki harga dan stok.',

            'variants.*.price.required' => 'Harga ukuran wajib diisi.',
            'variants.*.price.numeric' => 'Harga ukuran harus berupa angka.',
            'variants.*.price.min' => 'Harga ukuran tidak boleh kurang dari 0.',

            'variants.*.stock.required' => 'Stok ukuran wajib diisi.',
            'variants.*.stock.integer' => 'Stok ukuran harus berupa angka bulat.',
            'variants.*.stock.min' => 'Stok ukuran tidak boleh kurang dari 0.',
        ]);

        DB::transaction(function () use (
            $request,
            $validated,
            $product
        ) {
            $product->update([
                'product_code' => $validated['product_code'],
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug(
                    $validated['name'],
                    $product->id
                ),
                'description' => $validated['description'] ?? null,
                'material' => $validated['material'] ?? null,
                'status' => $validated['status'],
            ]);

            $sizeIds = collect($validated['size_ids'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $variantsData = $validated['variants'] ?? [];

            $color = Color::query()->first();

            if (!$color) {
                throw new \Exception(
                    'Tabel colors belum memiliki data.'
                );
            }

            $sizes = Size::query()
                ->whereIn('id', $sizeIds)
                ->orderBy('id')
                ->get();

            $existingVariants = $product->variants()
                ->with('inventory')
                ->get()
                ->keyBy('size_id');

            foreach ($sizes as $size) {
                $sizeId = (string) $size->id;

                $variantData = $variantsData[$sizeId]
                    ?? $variantsData[$size->id]
                    ?? null;

                if (!$variantData) {
                    throw new \Exception(
                        "Data harga dan stok untuk ukuran {$size->name} belum lengkap."
                    );
                }

                $sku = $product->product_code . '-' .
                    strtoupper(Str::slug($size->name));

                $variant = $existingVariants->get($size->id);

                if ($variant) {
                    $variant->update([
                        'color_id' => $variant->color_id ?: $color->id,
                        'sku' => $sku,
                        'price' => $variantData['price'],
                    ]);

                    if ($variant->inventory) {
                        $variant->inventory->update([
                            'stock' => $variantData['stock'],
                        ]);
                    } else {
                        $variant->inventory()->create([
                            'stock' => $variantData['stock'],
                        ]);
                    }
                } else {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'size_id' => $size->id,
                        'color_id' => $color->id,
                        'sku' => $sku,
                        'price' => $variantData['price'],
                    ]);

                    Inventory::create([
                        'product_variant_id' => $variant->id,
                        'stock' => $variantData['stock'],
                    ]);
                }
            }

            $variantsToRemove = $product->variants()
                ->whereNotIn('size_id', $sizeIds)
                ->with([
                    'inventory.stockMovements',
                    'transactionItems',
                ])
                ->get();

            foreach ($variantsToRemove as $variant) {
                if ($variant->transactionItems()->exists()) {
                    continue;
                }

                if ($variant->inventory) {
                    $variant->inventory
                        ->stockMovements()
                        ->delete();

                    $variant->inventory->delete();
                }

                $variant->delete();
            }

            if ($request->hasFile('image')) {
                $oldThumbnail = $product->images()
                    ->where('is_thumbnail', true)
                    ->first();

                if (
                    $oldThumbnail &&
                    Storage::disk('public')->exists(
                        $oldThumbnail->image
                    )
                ) {
                    Storage::disk('public')
                        ->delete($oldThumbnail->image);
                }

                $imagePath = $request
                    ->file('image')
                    ->store('products', 'public');

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
            $product->load([
                'variants.size',
                'variants.color',
                'variants.inventory',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui.',
                'data' => $product,
            ]);
        }

        return redirect()
            ->route('admin.products')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): JsonResponse
    {
        if (
            $product
                ->variants()
                ->whereHas('transactionItems')
                ->exists()
        ) {
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
                    if (
                        $image->image &&
                        Storage::disk('public')->exists(
                            $image->image
                        )
                    ) {
                        Storage::disk('public')
                            ->delete($image->image);
                    }

                    $image->delete();
                }

                foreach ($product->variants as $variant) {
                    if ($variant->inventory) {
                        $variant->inventory
                            ->stockMovements()
                            ->delete();

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
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $ignoreId
                    )
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}