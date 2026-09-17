<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductionRecord;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Http\JsonResponse;

class ProductionController extends Controller
{
    /**
     * Menampilkan daftar produksi
     */
    public function index()
    {
        $productions = ProductionRecord::with([
            'category',
            'items.size',
        ])
            ->latest()
            ->paginate(10);

        $totalProductions = ProductionRecord::count();

        $totalQuantity = ProductionRecord::sum('total_quantity');

        $completedProductions = ProductionRecord::where(
            'status',
            'completed'
        )->count();

        $inProgressProductions = ProductionRecord::where(
            'status',
            'in_progress'
        )->count();

        return view('admin.production.index', compact(
            'productions',
            'totalProductions',
            'totalQuantity',
            'completedProductions',
            'inProgressProductions'
        ));
    }

    /**
     * Menampilkan form tambah produksi
     */
    public function create()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        $sizes = Size::query()
            ->orderBy('id')
            ->get();

        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'product_code',
                'name',
                'status',
                'category_id',
            ]);

        return view('admin.production.create', compact(
            'categories',
            'sizes',
            'products'
        ));
    }

    /**
     * Menyimpan data produksi dan memperbarui stok produk
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_mode' => [
                'required',
                'in:existing,new',
            ],

            'product_id' => [
                'nullable',
                'required_if:product_mode,existing',
                'integer',
                'exists:products,id',
            ],

            'product_name' => [
                'nullable',
                'required_if:product_mode,new',
                'string',
                'max:150',
            ],

            'category_id' => [
                'nullable',
                'required_if:product_mode,new',
                'integer',
                'exists:categories,id',
            ],

            'period_type' => [
                'required',
                'in:daily,weekly,monthly,yearly',
            ],

            'period_start' => [
                'required',
                'date',
            ],

            'period_end' => [
                'required',
                'date',
                'after_or_equal:period_start',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'sizes' => [
                'required',
                'array',
            ],

            'sizes.*' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ], [
            'product_mode.required' =>
                'Jenis produk wajib dipilih.',

            'product_mode.in' =>
                'Jenis produk tidak valid.',

            'product_id.required_if' =>
                'Produk yang sudah ada wajib dipilih.',

            'product_id.exists' =>
                'Produk yang dipilih tidak ditemukan.',

            'product_name.required_if' =>
                'Nama produk baru wajib diisi.',

            'product_name.max' =>
                'Nama produk maksimal 150 karakter.',

            'category_id.required_if' =>
                'Kategori produk baru wajib dipilih.',

            'category_id.exists' =>
                'Kategori tidak ditemukan.',

            'period_type.required' =>
                'Jenis periode wajib dipilih.',

            'period_start.required' =>
                'Tanggal awal wajib diisi.',

            'period_end.required' =>
                'Tanggal akhir wajib diisi.',

            'period_end.after_or_equal' =>
                'Tanggal akhir harus sama atau setelah tanggal awal.',

            'sizes.required' =>
                'Data ukuran wajib diisi.',
        ]);

        $sizes = $validated['sizes'];

        $totalQuantity = collect($sizes)
            ->sum(fn ($quantity) => (int) ($quantity ?? 0));

        if ($totalQuantity <= 0) {
            return back()
                ->withErrors([
                    'sizes' =>
                        'Minimal satu ukuran harus memiliki jumlah produksi lebih dari 0.',
                ])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | 1. Tentukan produk
            |--------------------------------------------------------------------------
            */

            if ($validated['product_mode'] === 'existing') {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['product_id']);

                $productName = $product->name;
                $categoryId = $product->category_id;
            } else {
                $productName = $validated['product_name'];
                $categoryId = $validated['category_id'];

                $product = Product::create([
                    'product_code' => $this->generateProductCode(),
                    'category_id' => $categoryId,
                    'name' => $productName,
                    'slug' => $this->generateUniqueProductSlug($productName),
                    'description' => null,
                    'material' => null,

                    // Produk baru tidak langsung aktif
                    'status' => false,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Simpan data produksi
            |--------------------------------------------------------------------------
            */

            $production = ProductionRecord::create([
                'production_code' => $this->generateProductionCode(),

                'product_id' => $product->id,

                'period_type' => $validated['period_type'],

                'period_start' => $validated['period_start'],

                'period_end' => $validated['period_end'],

                'product_name' => $productName,

                'category_id' => $categoryId,

                'total_quantity' => $totalQuantity,

                // Stok langsung diperbarui ketika disimpan
                'status' => 'completed',

                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4. Perbarui stok setiap ukuran
            |--------------------------------------------------------------------------
            */

            foreach ($sizes as $sizeId => $quantity) {
                $quantity = (int) ($quantity ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $size = Size::query()->findOrFail($sizeId);

                // Simpan detail produksi
                $production->items()->create([
                    'size_id' => $size->id,
                    'quantity' => $quantity,
                ]);

                // Cari varian produk berdasarkan ukuran
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('size_id', $size->id)
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | 5. Jika varian belum ada, buat varian baru
                |--------------------------------------------------------------------------
                */

                if (!$variant) {
                    $sku = $product->product_code
                        . '-'
                        . strtoupper(Str::slug($size->name));

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'size_id' => $size->id,

                        // Warna tidak digunakan dalam proses produksi
                        'color_id' => null,

                        'sku' => $sku,

                        // Harga awal produk baru
                        'price' => 0,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 6. Tambahkan stok Inventory
                |--------------------------------------------------------------------------
                */

                $inventory = Inventory::query()
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->increment('stock', $quantity);
                } else {
                    Inventory::create([
                        'product_variant_id' => $variant->id,
                        'stock' => $quantity,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.productions')
                ->with(
                    'success',
                    'Produksi berhasil disimpan dan stok produk telah diperbarui.'
                );

        } catch (Throwable $e) {
            DB::rollBack();

            report($e);

            return back()
                ->withErrors([
                    'error' =>
                        'Terjadi kesalahan saat menyimpan produksi: '
                        . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    /**
     * Menampilkan detail produksi
     */
    public function show(ProductionRecord $production): View
    {
        $production->load([
            'category',
            'items.size',
        ]);

        return view('admin.production.show', compact(
            'production'
        ));
    }

    /**
     * Menampilkan form edit produksi
     */
    public function edit(ProductionRecord $production): View
    {
        $categories = Category::orderBy('name')->get();

        $sizes = Size::orderBy('id')->get();

        $production->load('items');

        $existingSizes = $production->items
            ->pluck('quantity', 'size_id')
            ->toArray();

        return view('admin.production.edit', compact(
            'production',
            'categories',
            'sizes',
            'existingSizes'
        ));
    }


    /**
     * Memperbarui data produksi dan menyesuaikan stok inventory
     */
    public function update(
        Request $request,
        ProductionRecord $production
    ): RedirectResponse {
        $validated = $request->validate([
            'period_type' => [
                'required',
                'in:daily,weekly,monthly,yearly',
            ],

            'period_start' => [
                'required',
                'date',
            ],

            'period_end' => [
                'required',
                'date',
                'after_or_equal:period_start',
            ],

            'product_name' => [
                'required',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'sizes' => [
                'required',
                'array',
            ],

            'sizes.*' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $newSizes = collect($validated['sizes'])
            ->mapWithKeys(function ($quantity, $sizeId) {
                return [
                    (int) $sizeId => (int) ($quantity ?? 0),
                ];
            })
            ->toArray();

        $newTotalQuantity = collect($newSizes)->sum();

        if ($newTotalQuantity <= 0) {
            return back()
                ->withErrors([
                    'sizes' =>
                        'Minimal satu ukuran harus memiliki jumlah produksi lebih dari 0.',
                ])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | 1. Kunci data produksi dan ambil detail lama
            |--------------------------------------------------------------------------
            */

            $production = ProductionRecord::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($production->id);

            $oldSizes = $production->items
                ->pluck('quantity', 'size_id')
                ->map(fn ($quantity) => (int) $quantity)
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | 2. Gabungkan seluruh ukuran lama dan ukuran baru
            |--------------------------------------------------------------------------
            */

            $sizeIds = collect(array_keys($oldSizes))
                ->merge(array_keys($newSizes))
                ->unique()
                ->values();


            /*
            |--------------------------------------------------------------------------
            | 3. Sesuaikan stok inventory berdasarkan selisih jumlah
            |--------------------------------------------------------------------------
            */

            foreach ($sizeIds as $sizeId) {
                $oldQuantity = (int) ($oldSizes[$sizeId] ?? 0);

                $newQuantity = (int) ($newSizes[$sizeId] ?? 0);

                $difference = $newQuantity - $oldQuantity;

                /*
                |--------------------------------------------------------------------------
                | Tidak ada perubahan jumlah
                |--------------------------------------------------------------------------
                */

                if ($difference === 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Pastikan ukuran tersedia
                |--------------------------------------------------------------------------
                */

                $size = Size::query()->findOrFail($sizeId);

                /*
                |--------------------------------------------------------------------------
                | Cari varian produk
                |--------------------------------------------------------------------------
                */

                $variant = ProductVariant::query()
                    ->where('product_id', $production->product_id)
                    ->where('size_id', $size->id)
                    ->lockForUpdate()
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Jika varian belum ada, buat varian baru
                |--------------------------------------------------------------------------
                */

                if (!$variant) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->findOrFail($production->product_id);

                    $sku = $product->product_code
                        . '-'
                        . strtoupper(Str::slug($size->name));

                    /*
                    |--------------------------------------------------------------------------
                    | Pastikan SKU tidak bentrok
                    |--------------------------------------------------------------------------
                    */

                    $baseSku = $sku;
                    $skuCounter = 1;

                    while (
                        ProductVariant::query()
                            ->where('sku', $sku)
                            ->exists()
                    ) {
                        $sku = $baseSku . '-' . $skuCounter;
                        $skuCounter++;
                    }

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'size_id' => $size->id,
                        'color_id' => null,
                        'sku' => $sku,
                        'price' => 0,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Cari atau buat inventory
                |--------------------------------------------------------------------------
                */

                $inventory = Inventory::query()
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Jika jumlah bertambah
                |--------------------------------------------------------------------------
                */

                if ($difference > 0) {
                    if ($inventory) {
                        $inventory->increment('stock', $difference);
                    } else {
                        Inventory::create([
                            'product_variant_id' => $variant->id,
                            'stock' => $difference,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Jika jumlah berkurang
                |--------------------------------------------------------------------------
                */

                if ($difference < 0) {
                    $decreaseAmount = abs($difference);

                    if (!$inventory) {
                        throw new \Exception(
                            'Data inventory untuk ukuran '
                            . $size->name
                            . ' tidak ditemukan.'
                        );
                    }

                    $currentStock = (int) $inventory->stock;

                    if ($currentStock < $decreaseAmount) {
                        throw new \Exception(
                            'Stok produk untuk ukuran '
                            . $size->name
                            . ' tidak mencukupi untuk mengurangi jumlah produksi.'
                        );
                    }

                    $inventory->update([
                        'stock' => $currentStock - $decreaseAmount,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Perbarui data utama produksi
            |--------------------------------------------------------------------------
            */

            $production->update([
                'period_type' => $validated['period_type'],

                'period_start' => $validated['period_start'],

                'period_end' => $validated['period_end'],

                'product_name' => $validated['product_name'],

                'category_id' => $validated['category_id'],

                'total_quantity' => $newTotalQuantity,

                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 5. Hapus detail ukuran lama
            |--------------------------------------------------------------------------
            */

            $production->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | 6. Simpan detail ukuran terbaru
            |--------------------------------------------------------------------------
            */

            foreach ($newSizes as $sizeId => $quantity) {
                if ($quantity <= 0) {
                    continue;
                }

                $production->items()->create([
                    'size_id' => $sizeId,

                    'quantity' => $quantity,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.productions')
                ->with(
                    'success',
                    'Data produksi dan stok produk berhasil diperbarui.'
                );

        } catch (Throwable $e) {
            DB::rollBack();

            report($e);

            return back()
                ->withErrors([
                    'error' =>
                        'Terjadi kesalahan saat memperbarui data produksi: '
                        . $e->getMessage(),
                ])
                ->withInput();
        }
    }


    /**
     * Menghapus data produksi dan mengurangi stok produk
     */
    public function destroy(
        Request $request,
        ProductionRecord $production
    ): RedirectResponse|JsonResponse {
        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | 1. Ambil data produksi beserta detail ukuran
            |--------------------------------------------------------------------------
            */

            $production = ProductionRecord::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($production->id);

            if (!$production->product_id) {
                throw new \Exception(
                    'Produksi tidak memiliki produk yang terhubung.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Kunci data produk
            |--------------------------------------------------------------------------
            */

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($production->product_id);

            /*
            |--------------------------------------------------------------------------
            | 3. Kurangi stok berdasarkan detail produksi
            |--------------------------------------------------------------------------
            */

            foreach ($production->items as $item) {
                $quantity = (int) $item->quantity;

                if ($quantity <= 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Cari varian berdasarkan produk dan ukuran
                |--------------------------------------------------------------------------
                */

                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('size_id', $item->size_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new \Exception(
                        'Varian produk untuk ukuran ID '
                        . $item->size_id
                        . ' tidak ditemukan.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Cari inventory
                |--------------------------------------------------------------------------
                */

                $inventory = Inventory::query()
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw new \Exception(
                        'Data inventory untuk ukuran ID '
                        . $item->size_id
                        . ' tidak ditemukan.'
                    );
                }

                $currentStock = (int) $inventory->stock;

                /*
                |--------------------------------------------------------------------------
                | Validasi stok
                |--------------------------------------------------------------------------
                */

                if ($currentStock < $quantity) {
                    throw new \Exception(
                        'Stok produk '
                        . $product->name
                        . ' untuk ukuran ID '
                        . $item->size_id
                        . ' tidak mencukupi untuk menghapus produksi.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Kurangi stok
                |--------------------------------------------------------------------------
                */

                $inventory->update([
                    'stock' => $currentStock - $quantity,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Hapus data produksi
            |--------------------------------------------------------------------------
            */

            $production->delete();

            DB::commit();

            $successMessage =
                'Data produksi berhasil dihapus dan stok produk telah diperbarui.';

            /*
            |--------------------------------------------------------------------------
            | 5. Respons JSON untuk fetch / AJAX
            |--------------------------------------------------------------------------
            */

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Respons normal untuk form biasa
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('admin.productions')
                ->with(
                    'success',
                    $successMessage
                );

        } catch (Throwable $e) {
            DB::rollBack();

            report($e);

            $errorMessage =
                'Terjadi kesalahan saat menghapus produksi: '
                . $e->getMessage();

            /*
            |--------------------------------------------------------------------------
            | Respons error JSON
            |--------------------------------------------------------------------------
            */

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Respons error normal
            |--------------------------------------------------------------------------
            */

            return back()
                ->withErrors([
                    'error' => $errorMessage,
                ]);
        }
    }

    /**
     * Generate kode produksi otomatis
     */
    private function generateProductionCode(): string
    {
        $date = now()->format('Ymd');

        $prefix = 'PRD-' . $date . '-';

        $latest = ProductionRecord::where(
            'production_code',
            'like',
            $prefix . '%'
        )
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($latest) {
            $lastNumber = (int) str_replace(
                $prefix,
                '',
                $latest->production_code
            );

            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );
    }
    /**
     * Generate kode produk otomatis
     */
    private function generateProductCode(): string
    {
        $latest = Product::query()
            ->where('product_code', 'like', 'PRD-%')
            ->orderByDesc('id')
            ->value('product_code');

        $nextNumber = 1;

        if (
            $latest &&
            preg_match('/PRD-(\d+)/', $latest, $matches)
        ) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return 'PRD-' . str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate slug produk yang unik
     */
    private function generateUniqueProductSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::query()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}