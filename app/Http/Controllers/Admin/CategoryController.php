<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori.
     */
    public function index(Request $request): View
    {
        $query=Category::withCount('products');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where('name', 'like', "%{$search}%");
        }

        if($request->filled('status')){
            $query->where(
                'status',
                $request->input('status')==='Aktif'
            );
        }

        $categories=$query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $thisMonthStart=now()->startOfMonth();
        $lastMonthStart=now()->subMonth()->startOfMonth();
        $lastMonthEnd=now()->subMonth()->endOfMonth();

        $totalCategories=Category::count();

        $totalCategoriesThisMonth=Category::whereBetween('created_at',[
            $thisMonthStart,
            now()
        ])->count();

        $totalCategoriesLastMonth=Category::whereBetween('created_at',[
            $lastMonthStart,
            $lastMonthEnd
        ])->count();

        $activeCategories=Category::where('status',true)->count();

        $activeCategoriesThisMonth=Category::where('status',true)
            ->whereBetween('created_at',[
                $thisMonthStart,
                now()
            ])
            ->count();

        $activeCategoriesLastMonth=Category::where('status',true)
            ->whereBetween('created_at',[
                $lastMonthStart,
                $lastMonthEnd
            ])
            ->count();

        $activeProducts=\App\Models\Product::where('status',true)->count();

        $activeProductsThisMonth=\App\Models\Product::where('status',true)
            ->whereBetween('created_at',[
                $thisMonthStart,
                now()
            ])
            ->count();

        $activeProductsLastMonth=\App\Models\Product::where('status',true)
            ->whereBetween('created_at',[
                $lastMonthStart,
                $lastMonthEnd
            ])
            ->count();

        $categoryGrowth=$this->calculateGrowth(
            $totalCategoriesThisMonth,
            $totalCategoriesLastMonth
        );

        $activeCategoryGrowth=$this->calculateGrowth(
            $activeCategoriesThisMonth,
            $activeCategoriesLastMonth
        );

        $activeProductGrowth=$this->calculateGrowth(
            $activeProductsThisMonth,
            $activeProductsLastMonth
        );

        return view('admin.categories.index',[
            'categories'=>$categories,
            'totalCategories'=>$totalCategories,
            'activeCategories'=>$activeCategories,
            'activeProducts'=>$activeProducts,
            'categoryGrowth'=>$categoryGrowth,
            'activeCategoryGrowth'=>$activeCategoryGrowth,
            'activeProductGrowth'=>$activeProductGrowth,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        if ($search === '') {
            return response()->json([
                'data' => []
            ]);
        }

        $categories = Category::query()
            ->where('name', 'like', '%' . $search . '%')
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'name',
                'slug',
            ]);

        return response()->json([
            'data' => $categories
        ]);
    }

    private function calculateGrowth(int $current,int $previous): string
    {
        if($previous===0){
            return $current>0?'+100%':'0%';
        }

        $growth=(($current-$previous)/$previous)*100;

        return ($growth>=0?'+':'').number_format($growth,1).'%';
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'slug' => ['required', 'string', 'max:120', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'slug.required' => 'Slug kategori wajib diisi.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'slug.max' => 'Slug kategori maksimal 120 karakter.',
            'status.required' => 'Status kategori wajib dipilih.',
        ]);

        $category = DB::transaction(function () use ($validated) {
            return Category::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['slug']),
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat.',
                'data' => $category,
            ], 201);
        }

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Kategori berhasil dibuat.');
    }

    public function show(Category $category): View
    {
        $category->loadCount('products');

        return view('admin.categories.show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name,' . $category->id,
            ],
            'slug' => [
                'required',
                'string',
                'max:120',
                'unique:categories,slug,' . $category->id,
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'slug.required' => 'Slug kategori wajib diisi.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'slug.max' => 'Slug kategori maksimal 120 karakter.',
            'status.required' => 'Status kategori wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $category) {
            $category->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['slug']),
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui.',
                'data' => $category->fresh(),
            ], 200);
        }

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): JsonResponse|RedirectResponse
    {
        if ($category->products()->exists()) {
            $message = 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.';

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.categories')
                ->with('error', $message);
        }

        $category->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus.',
                'data' => [
                    'id' => $category->id,
                ],
            ]);
        }

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Membuat slug unik.
     */
    private function generateUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Category::where('slug', $slug)
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