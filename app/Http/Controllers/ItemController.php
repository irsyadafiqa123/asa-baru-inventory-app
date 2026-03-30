<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::with('category')->withSum(
            ['transactiondetails as total_masuk' => function ($q) {
                $q->whereHas('transaction', function ($q2) {
                    $q2->where('transaction_type', 'masuk');
                });
            }],
            'amount'
        )->withSum(
            ['transactiondetails as total_keluar' => function ($q) {
                $q->whereHas('transaction', function ($q2) {
                    $q2->where('transaction_type', 'keluar');
                });
            }],
            'amount'
        );

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter 
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('category_id') && is_numeric($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $query->oldest();
                    break;
                case 'name_asc':
                    $query->orderBy('item_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('item_name', 'desc');
                    break;
                case 'price_low':
                    $query->orderBy('selling_price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('selling_price', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        $items = $query->paginate(10)->withQueryString();
        $categories = Category::all();

        return view('pages.data_barang', compact('items', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'item_name' => 'required|string|max:255|unique:items,item_name',
                'category_id' => 'required',
                'new_category' => 'nullable|required_if:category_id,other|string|max:255|unique:categories,category_name',
                'selling_price' => 'required|numeric|min:0|gte:capital_price',
                'capital_price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0'
            ],
            [
                // Item Name
                'item_name.required' => 'Nama barang wajib diisi.',
                'item_name.string' => 'Nama barang harus berupa teks.',
                'item_name.max' => 'Nama barang maksimal 255 karakter.',
                'item_name.unique' => 'Nama barang sudah terdaftar.',

                // Category
                'category_id.required' => 'Kategori wajib dipilih.',

                // New Category
                'new_category.required_if' => 'Kategori baru wajib diisi.',
                'new_category.string' => 'Kategori baru harus berupa teks.',
                'new_category.max' => 'Kategori baru maksimal 255 karakter.',
                'new_category.unique' => 'Kategori sudah ada.',

                // Capital Price
                'capital_price.required' => 'Harga modal wajib diisi.',
                'capital_price.numeric' => 'Harga modal harus berupa angka.',
                'capital_price.min' => 'Harga modal tidak boleh kurang dari 0.',

                // Selling Price
                'selling_price.required' => 'Harga jual wajib diisi.',
                'selling_price.numeric' => 'Harga jual harus berupa angka.',
                'selling_price.min' => 'Harga jual tidak boleh kurang dari 0.',
                'selling_price.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',

                // Stock
                'stock.required' => 'Stok barang wajib diisi.',
                'stock.integer' => 'Stok harus berupa angka bulat.',
                'stock.min' => 'Stok tidak boleh kurang dari 0.'
            ]
        );

        // Flash Session to Open Add Modal Form if Error
        if ($validator->fails()) {
            return redirect()->route('barang.index')
                ->withErrors($validator, 'addItemError')
                ->withInput()
                ->with('modal', 'addItem');
        }

        $validated = $validator->validated();

        try {
            // Added to Stock In Item
            DB::transaction(function () use ($validated, $request) {
                // Handle New Category
                $categoryId = $validated['category_id'];

                if ($categoryId === 'other') {

                    $category = Category::create([
                        'category_name' => $request->new_category,
                        'information' => 'none'
                    ]);

                    $categoryId = $category->id;
                }

                $item = Item::create([
                    'item_name' => $validated['item_name'],
                    'category_id' => $categoryId,
                    'capital_price' => $validated['capital_price'],
                    'selling_price' => $validated['selling_price'],
                    'stock' => $validated['stock']
                ]);

                if ($item->stock > 0) {
                    $transaction = Transaction::create([
                        'user_id' => Auth::id(),
                        'transaction_date' => now(),
                        'transaction_type' => 'masuk'
                    ]);

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'amount' => $item->stock,
                        'capital_price' => $item->capital_price,
                        'selling_price' => $item->selling_price,
                        'subtotal' => $item->stock * $item->capital_price
                    ]);
                }
            });

            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('barang.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'item_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('items', 'item_name')->ignore($id)
                ],
                'category_id' => 'required|exists:categories,id',
                'capital_price' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0|gte:capital_price',

            ],
            [
                // Item Name
                'item_name.required' => 'Nama barang wajib diisi.',
                'item_name.string' => 'Nama barang harus berupa teks.',
                'item_name.max' => 'Nama barang maksimal 255 karakter.',
                'item_name.unique' => 'Nama barang sudah digunakan oleh barang lain.',

                // Category
                'category_id.required' => 'Kategori wajib dipilih.',
                'category_id.exists' => 'Kategori yang dipilih tidak valid.',

                // Capital Price
                'capital_price.required' => 'Harga modal wajib diisi.',
                'capital_price.numeric' => 'Harga modal harus berupa angka.',
                'capital_price.min' => 'Harga modal tidak boleh kurang dari 0.',

                // Selling Price
                'selling_price.required' => 'Harga jual wajib diisi.',
                'selling_price.numeric' => 'Harga jual harus berupa angka.',
                'selling_price.min' => 'Harga jual tidak boleh kurang dari 0.',
                'selling_price.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',
            ]
        );

        // Flash Session to Open Edit Modal Form if Error
        if ($validator->fails()) {
            return redirect()->route('barang.index')
                ->withErrors($validator, 'editItemError')
                ->withInput()
                ->with('modal', 'editItem')
                ->with('editId', $id);;
        }

        $validated = $validator->validated();

        $item = Item::findOrFail($id);

        try {
            $item->update($validated);

            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil diubah');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('barang.index')
                ->with('error', 'Terjadi kesalahan saat merubah data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);

        try {
            DB::transaction(function () use ($item) {

                $transactionIds = TransactionDetail::where('item_id', $item->id)
                    ->pluck('transaction_id');

                $item->delete();

                Transaction::whereIn('id', $transactionIds)
                    ->doesntHave('transactionDetails')
                    ->delete();
            });

            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil dihapus');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('barang.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }
}
