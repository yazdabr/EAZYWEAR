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

        $sizes = Size::ordered()->get();

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
    /**
     * Menyimpan laporan produksi tanpa mengubah produk atau stok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_mode' => ['required', 'in:existing,new'],
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
            'period_type' => ['required', 'in:daily,weekly,monthly,yearly'],
            'period_start' => ['required', 'date'],
            'period_end' => [
                'required',
                'date',
                'after_or_equal:period_start',
            ],
            'notes' => ['nullable', 'string'],
            'regular_sizes' => ['required', 'array'],
            'regular_sizes.*' => ['nullable', 'integer', 'min:0'],
            'longsleeve_sizes' => ['required', 'array'],
            'longsleeve_sizes.*' => ['nullable', 'integer', 'min:0'],
        ], [
            'product_mode.required' => 'Jenis produk wajib dipilih.',
            'product_id.required_if' => 'Produk yang sudah ada wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            'product_name.required_if' => 'Nama produk baru wajib diisi.',
            'category_id.required_if' => 'Kategori produk baru wajib dipilih.',
            'period_type.required' => 'Jenis periode wajib dipilih.',
            'period_start.required' => 'Tanggal awal wajib diisi.',
            'period_end.required' => 'Tanggal akhir wajib diisi.',
            'period_end.after_or_equal' =>
                'Tanggal akhir harus sama atau setelah tanggal awal.',
        ]);

        $regularSizes = collect($validated['regular_sizes'])
            ->mapWithKeys(fn ($quantity, $sizeId) => [
                (int) $sizeId => (int) ($quantity ?? 0),
            ]);

        $longsleeveSizes = collect($validated['longsleeve_sizes'])
            ->mapWithKeys(fn ($quantity, $sizeId) => [
                (int) $sizeId => (int) ($quantity ?? 0),
            ]);

        $sizeIds = $regularSizes->keys()
            ->merge($longsleeveSizes->keys())
            ->unique()
            ->values();

        $totalQuantity = $sizeIds->sum(
            fn ($sizeId) =>
                $regularSizes->get($sizeId, 0)
                + $longsleeveSizes->get($sizeId, 0)
        );

        if ($totalQuantity <= 0) {
            return back()
                ->withErrors([
                    'regular_sizes' =>
                        'Minimal satu ukuran harus memiliki jumlah produksi lebih dari 0.',
                ])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            if ($validated['product_mode'] === 'existing') {
                $product = Product::query()
                    ->findOrFail($validated['product_id']);

                $productId = $product->id;
                $productName = $product->name;
                $categoryId = $product->category_id;
            } else {
                // Produk baru hanya dicatat di laporan.
                // Tidak membuat data pada tabel products.
                $productId = null;
                $productName = $validated['product_name'];
                $categoryId = $validated['category_id'];
            }

            $production = ProductionRecord::create([
                'production_code' => $this->generateProductionCode(),
                'product_id' => $productId,
                'period_type' => $validated['period_type'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'product_name' => $productName,
                'category_id' => $categoryId,
                'total_quantity' => $totalQuantity,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($sizeIds as $sizeId) {
                $size = Size::query()->findOrFail($sizeId);

                $regularQuantity = (int) $regularSizes->get($sizeId, 0);
                $longsleeveQuantity = (int) $longsleeveSizes->get($sizeId, 0);

                if ($regularQuantity > 0) {
                    $production->items()->create([
                        'size_id' => $size->id,
                        'quantity' => $regularQuantity,
                        'is_longsleeve' => false,
                    ]);
                }

                if ($longsleeveQuantity > 0) {
                    $production->items()->create([
                        'size_id' => $size->id,
                        'quantity' => $longsleeveQuantity,
                        'is_longsleeve' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.productions')
                ->with('success', 'Laporan produksi berhasil disimpan.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors([
                    'error' =>
                        'Terjadi kesalahan saat menyimpan laporan produksi. Silakan coba lagi.',
                ])
                ->withInput();
        }
    }
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

        $sizes = Size::ordered()->get();

        $production->load('items');

        $existingRegularSizes = [];
        $existingLongsleeveSizes = [];

        foreach ($production->items as $item) {
            $sizeId = (int) $item->size_id;

            if ($item->is_longsleeve) {
                $existingLongsleeveSizes[$sizeId] =
                    ($existingLongsleeveSizes[$sizeId] ?? 0)
                    + (int) $item->quantity;
            } else {
                $existingRegularSizes[$sizeId] =
                    ($existingRegularSizes[$sizeId] ?? 0)
                    + (int) $item->quantity;
            }
        }

        return view('admin.production.edit', compact(
            'production',
            'categories',
            'sizes',
            'existingRegularSizes',
            'existingLongsleeveSizes'
        ));
    }


    /**
     * Memperbarui data produksi dan menyesuaikan stok inventory
     */
    /**
     * Memperbarui laporan produksi tanpa mengubah stok.
     */
    public function update(
        Request $request,
        ProductionRecord $production
    ): RedirectResponse {
        $validated = $request->validate([
            'period_type' => ['required', 'in:daily,weekly,monthly,yearly'],
            'period_start' => ['required', 'date'],
            'period_end' => [
                'required',
                'date',
                'after_or_equal:period_start',
            ],
            'product_name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'notes' => ['nullable', 'string'],
            'regular_sizes' => ['required', 'array'],
            'regular_sizes.*' => ['nullable', 'integer', 'min:0'],
            'longsleeve_sizes' => ['required', 'array'],
            'longsleeve_sizes.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $regularSizes = collect($validated['regular_sizes'])
            ->mapWithKeys(fn ($quantity, $sizeId) => [
                (int) $sizeId => (int) ($quantity ?? 0),
            ]);

        $longsleeveSizes = collect($validated['longsleeve_sizes'])
            ->mapWithKeys(fn ($quantity, $sizeId) => [
                (int) $sizeId => (int) ($quantity ?? 0),
            ]);

        $sizeIds = $regularSizes->keys()
            ->merge($longsleeveSizes->keys())
            ->unique()
            ->values();

        $newTotalQuantity = $sizeIds->sum(
            fn ($sizeId) =>
                $regularSizes->get($sizeId, 0)
                + $longsleeveSizes->get($sizeId, 0)
        );

        if ($newTotalQuantity <= 0) {
            return back()
                ->withErrors([
                    'regular_sizes' =>
                        'Minimal satu ukuran harus memiliki jumlah produksi lebih dari 0.',
                ])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $production = ProductionRecord::query()
                ->lockForUpdate()
                ->findOrFail($production->id);

            // Hanya memperbarui informasi laporan.
            $production->update([
                'period_type' => $validated['period_type'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'product_name' => $validated['product_name'],
                'category_id' => $validated['category_id'],
                'total_quantity' => $newTotalQuantity,
                'notes' => $validated['notes'] ?? null,
            ]);

            $production->items()->delete();

            foreach ($sizeIds as $sizeId) {
                $size = Size::query()->findOrFail($sizeId);

                $regularQuantity = (int) $regularSizes->get($sizeId, 0);
                $longsleeveQuantity = (int) $longsleeveSizes->get($sizeId, 0);

                if ($regularQuantity > 0) {
                    $production->items()->create([
                        'size_id' => $size->id,
                        'quantity' => $regularQuantity,
                        'is_longsleeve' => false,
                    ]);
                }

                if ($longsleeveQuantity > 0) {
                    $production->items()->create([
                        'size_id' => $size->id,
                        'quantity' => $longsleeveQuantity,
                        'is_longsleeve' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.productions')
                ->with('success', 'Laporan produksi berhasil diperbarui.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors([
                    'error' =>
                        'Terjadi kesalahan saat memperbarui laporan produksi. Silakan coba lagi.',
                ])
                ->withInput();
        }
    }
    /**
     * Menghapus laporan produksi tanpa mengubah stok.
     */
    public function destroy(
        Request $request,
        ProductionRecord $production
    ): RedirectResponse|JsonResponse {
        DB::beginTransaction();

        try {
            $production = ProductionRecord::query()
                ->lockForUpdate()
                ->findOrFail($production->id);

            $production->items()->delete();
            $production->delete();

            DB::commit();

            $successMessage = 'Laporan produksi berhasil dihapus.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                ]);
            }

            return redirect()
                ->route('admin.productions')
                ->with('success', $successMessage);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            $errorMessage =
                'Terjadi kesalahan saat menghapus laporan produksi. Silakan coba lagi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            return back()->withErrors([
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
