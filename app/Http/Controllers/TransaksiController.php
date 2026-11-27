<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->query('jenis');
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        // Filter tanggal diabaikan bila formatnya tidak valid.
        if (! \is_string($dari) || ! \strtotime($dari)) {
            $dari = null;
        }
        if (! \is_string($sampai) || ! \strtotime($sampai)) {
            $sampai = null;
        }

        $transaksis = Transaksi::query()
            ->when(in_array($jenis, Transaksi::JENIS, true), function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })
            ->when($dari, fn ($q) => $q->where('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->where('tanggal', '<=', $sampai))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('transaksi.index', compact('transaksis', 'jenis', 'dari', 'sampai'));
    }

    public function create()
    {
        return view('transaksi.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTransaksi($request);

        $validated['kode_transaksi'] = $this->generateKodeTransaksi($validated['tanggal']);

        Transaksi::create($validated);

        return redirect()
            ->route('transaksi.index')
            ->with('success', 'Transaksi '.$validated['kode_transaksi'].' berhasil dicatat.');
    }

    public function edit(Transaksi $transaksi)
    {
        return view('transaksi.edit', compact('transaksi'));
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $validated = $this->validateTransaksi($request);

        $transaksi->update($validated);

        return redirect()
            ->route('transaksi.index')
            ->with('success', 'Transaksi '.$transaksi->kode_transaksi.' berhasil diperbarui.');
    }

    public function destroy(Transaksi $transaksi)
    {
        $kode = $transaksi->kode_transaksi;
        $transaksi->delete();

        return redirect()
            ->route('transaksi.index')
            ->with('success', 'Transaksi '.$kode.' berhasil dihapus.');
    }

    private function validateTransaksi(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:'.\implode(',', Transaksi::JENIS)],
            'nominal' => ['required', 'integer', 'min:1'],
        ], [], [
            'tanggal' => 'tanggal',
            'keterangan' => 'keterangan',
            'nominal' => 'nominal',
            'jenis' => 'jenis transaksi',
        ]);
    }

    private function generateKodeTransaksi(string $tanggal): string
    {
        // Logika kode dipindahkan ke model agar dipakai bersama
        // dengan PemesananController (pemasukan otomatis).
        return Transaksi::generateKode($tanggal);
    }
}
