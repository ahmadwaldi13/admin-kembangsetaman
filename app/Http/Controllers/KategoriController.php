<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        // untuk mengatur tampilan pada kategori/index.blade.php
        $kategori = Kategori::orderby('nama_kategori')->paginate(10);
        return view('kategori.index', compact('kategori'));
    }

    public function getAllCategories()
    {
        $kategori = Kategori::all();
        return response()->json($kategori);
    }
    public function searchCategories(Request $request)
{
    $query = $request->query('category');

    // Cari kategori berdasarkan query dan load produk serta data tambahan terkait
    $products = Kategori::where('nama_kategori', 'LIKE', '%' . $query . '%')
        ->with(['produk.tambahan'])  // Load produk dan data tambahan terkait
        ->get()
        ->flatMap(function($item) {
            return $item->produk->map(function($produk) {
                return [
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama_produk,
                    'foto_utama' => $produk->foto_utama,
                    'deskripsi' => $produk->deskripsi,
                ];
            });
        });

    return response()->json($products);
}


    public function store(Request $request)
    {
        // Untuk store data dari modal  kategori/index.blade.php ke db
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'kode_kategori' => 'required|string|max:255',
            'deskripsi_kategori' => 'required|string|max:255',
        ]);

        Kategori::create($request->all());

        return response()->json(['success' => 'Kategori berhasil ditambahkan.']);
    }

    public function update(Request $request, $id_kategori)
    {
        //  update data dari modal edit kategori/index.blade.php ke db
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'kode_kategori' => 'required|string|max:255',
            'deskripsi_kategori' => 'required|string|max:255',
        ]);

        $kategori = Kategori::find($id_kategori);
        $kategori->update($request->all());

        return response()->json(['success' => 'Kategori berhasil diupdate.']);
    }

    public function destroy($id_kategori)
    {
        // hapus data kategori kategori/index.blade.php

        $kategori = Kategori::findOrFail($id_kategori);
        if ($kategori->produk->count() > 0) {
            return redirect()->back()->with('error', 'Kategori tidak bisa dihapus karena memiliki Produk yang bersangkutan. Harap hapus Produk terlebih dahulu.');
        }

        $kategori->delete();
        // Kategori::destroy($id_kategori);
    
        return back()->with(['success' => 'Kategori berhasil dihapus.']);
    }

    public function search(Request $request)
    {
        // untuk search kategori menggunakan select2  dari produk/index.blade.php
        $data = Kategori::where('nama_kategori','LIKE','%'.request('q').'%')->orderBy("nama_kategori","asc")->get();
        return response()->json($data);
    }
}
