<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Models\ProductVariant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SizeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Size::withCount('productVariants');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where('name', 'like', "%{$search}%");
        }

        $sizes = $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.sizes.index', [
            'sizes' => $sizes,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:sizes,name',
                ],
            ],
            [
                'name.required' => 'Nama ukuran wajib diisi.',
                'name.string' => 'Nama ukuran harus berupa teks.',
                'name.max' => 'Nama ukuran maksimal 20 karakter.',
                'name.unique' => 'Nama ukuran tersebut sudah tersedia.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data ukuran tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $size = Size::create([
            'name' => trim($request->input('name')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ukuran berhasil ditambahkan.',
            'data' => $size,
        ], 201);
    }

    public function show(Size $size)
    {
        return response()->json([
            'success' => true,
            'data' => $size,
        ]);
    }

    public function edit(Size $size)
    {
        return view('admin.sizes.edit', [
            'size' => $size,
        ]);
    }

    public function update(Request $request, Size $size)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:sizes,name,' . $size->id,
                ],
            ],
            [
                'name.required' => 'Nama ukuran wajib diisi.',
                'name.string' => 'Nama ukuran harus berupa teks.',
                'name.max' => 'Nama ukuran maksimal 20 karakter.',
                'name.unique' => 'Nama ukuran tersebut sudah tersedia.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data ukuran tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $size->update([
            'name' => trim($request->input('name')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ukuran berhasil diperbarui.',
            'data' => $size,
        ]);
    }

    public function destroy(Size $size)
    {
        $used = ProductVariant::where('size_id', $size->id)->exists();

        if ($used) {
            return response()->json([
                'message' => "Ukuran {$size->name} tidak dapat dihapus karena masih digunakan oleh produk."
            ], 422);
        }

        try {
            $size->delete();

            return response()->json([
                'message' => 'Ukuran berhasil dihapus.'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => "Ukuran {$size->name} tidak dapat dihapus karena masih digunakan oleh produk."
            ], 422);
        }
    }
}