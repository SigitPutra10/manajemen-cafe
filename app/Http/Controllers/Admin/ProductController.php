<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id', '');
        $availability = (string) $request->query('availability', '');

        $products = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($categoryId !== '' && $categoryId !== null, function ($query) use ($categoryId) {
                $query->where('category_id', (int) $categoryId);
            })
            ->when($availability === 'available', function ($query) {
                $query->where('is_available', true);
            })
            ->when($availability === 'unavailable', function ($query) {
                $query->where('is_available', false);
            })
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => Product::query()->count(),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId !== null ? (string) $categoryId : '',
                'availability' => $availability,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:0', 'max:99999999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['nullable', 'boolean'],
        ], [
            'category_id.required' => 'Kategori menu wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'name.required' => 'Nama menu wajib diisi.',
            'name.max' => 'Nama menu maksimal 150 karakter.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga tidak boleh kurang dari 0.',
            'price.max' => 'Harga terlalu besar.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::query()->create([
            'category_id' => (int) $validated['category_id'],
            'name' => $validated['name'],
            'price' => (int) $validated['price'],
            'image' => $imagePath,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Menu \"{$product->name}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:0', 'max:99999999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['nullable', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'category_id.required' => 'Kategori menu wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'name.required' => 'Nama menu wajib diisi.',
            'name.max' => 'Nama menu maksimal 150 karakter.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga tidak boleh kurang dari 0.',
            'price.max' => 'Harga terlalu besar.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('products', 'public');
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $newImagePath;
        } elseif ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $imagePath = null;
        }

        $product->update([
            'category_id' => (int) $validated['category_id'],
            'name' => $validated['name'],
            'price' => (int) $validated['price'],
            'image' => $imagePath,
            'is_available' => $request->boolean('is_available'),
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Menu \"{$product->name}\" berhasil diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Menu \"{$name}\" berhasil dihapus.");
    }

    public function toggleAvailability(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $product->update([
            'is_available' => ! $product->is_available,
        ]);

        $statusLabel = $product->is_available ? 'Tersedia' : 'Stok Habis';
        $message = "Status \"{$product->name}\" diubah menjadi {$statusLabel}.";

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $product->id,
                'is_available' => $product->is_available,
                'status_label' => $statusLabel,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('status', $message);
    }
}
