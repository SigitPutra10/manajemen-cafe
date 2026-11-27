<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::withCount('menus')->orderBy('nama')->get();

        return view('kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:kategorias,nama'],
        ], [], ['nama' => 'nama kategori']);

        Kategori::create($validated);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori "'.$validated['nama'].'" berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:kategorias,nama,'.$kategori->id],
        ], [], ['nama' => 'nama kategori']);

        $kategori->update($validated);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori "'.$validated['nama'].'" berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->menus()->exists()) {
            return redirect()
                ->route('kategori.index')
                ->with('error', 'Kategori "'.$kategori->nama.'" tidak bisa dihapus karena masih digunakan oleh '.$kategori->menus()->count().' menu.');
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori "'.$nama.'" berhasil dihapus.');
    }
}
