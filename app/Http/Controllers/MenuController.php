<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $kategori = $request->query('kategori');

        $menus = Menu::query()
            ->with('kategori')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_menu', 'like', '%'.$search.'%');
            })
            ->when($request->filled('kategori'), function ($query) use ($kategori) {
                $query->where('kategori_id', $kategori);
            })
            ->orderBy('nama_menu')
            ->paginate(10)
            ->withQueryString();

        $semuaKategori = Kategori::orderBy('nama')->get();

        return view('menu.index', compact('menus', 'search', 'kategori', 'semuaKategori'));
    }

    public function create()
    {
        return view('menu.create', [
            'semuaKategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMenu($request);

        DB::transaction(function () use (&$validated, $request) {
            $validated['gambar'] = $this->simpanGambar($request);
            Menu::create($validated);
        });

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu "'.$validated['nama_menu'].'" berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        return view('menu.edit', [
            'menu' => $menu,
            'semuaKategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $this->validateMenu($request, $menu->id);

        DB::transaction(function () use (&$validated, $request, $menu) {
            $gambarBaru = $this->simpanGambar($request, $menu);

            if ($gambarBaru !== null) {
                $validated['gambar'] = $gambarBaru;
            }

            $menu->update($validated);
        });

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu "'.$menu->nama_menu.'" berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $nama = $menu->nama_menu;

        DB::transaction(function () use ($menu) {
            if ($menu->gambar) {
                $path = \public_path('uploads/menus/'.$menu->gambar);
                if (\file_exists($path)) {
                    \unlink($path);
                }
            }

            $menu->delete();
        });

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu "'.$nama.'" berhasil dihapus.');
    }

    /**
     * Toggle cepat status Event/Nonaktif menu via AJAX/Fetch.
     */
    public function toggle(Request $request, Menu $menu)
    {
        $menu->is_event = ! $menu->is_event;
        $menu->save();

        return response()->json([
            'success' => true,
            'is_event' => $menu->is_event,
            'message' => $menu->is_event
                ? 'Menu "'.$menu->nama_menu.'" diaktifkan sebagai Event.'
                : 'Menu "'.$menu->nama_menu.'" dinonaktifkan.',
        ]);
    }

    private function validateMenu(Request $request, ?int $menuId = null): array
    {
        return $request->validate([
            'nama_menu' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'integer', 'exists:kategorias,id'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'is_event' => ['nullable', 'boolean'],
            'gambar' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2 MB
            ],
        ], [
            'gambar.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
        ], [
            'nama_menu' => 'nama menu',
            'kategori_id' => 'kategori',
            'harga' => 'harga',
            'stok' => 'stok',
            'gambar' => 'gambar',
        ]);
    }

    /**
     * Simpan gambar ke public/uploads/menus dengan nama unik.
     * Mengembalikan nama file, null bila tidak ada upload (pakai placeholder),
     * atau string kosong bila upload dibatalkan saat edit.
     */
    private function simpanGambar(Request $request, ?Menu $menu = null): ?string
    {
        if (! $request->hasFile('gambar') || ! $request->file('gambar')->isValid()) {
            return $menu ? null : '';
        }

        // Hapus file lama saat mengganti gambar.
        if ($menu && $menu->gambar) {
            $lama = \public_path('uploads/menus/'.$menu->gambar);
            if (\file_exists($lama)) {
                \unlink($lama);
            }
        }

        $file = $request->file('gambar');
        $nama = \sha1($file->getClientOriginalName().\uniqid('', true)).'.'.$file->guessExtension();

        $file->move(\public_path('uploads/menus'), $nama);

        return $nama;
    }
}
