<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TransactionDetail::with(['transaction', 'item.category'])
            ->whereHas('transaction', function ($q) {
                $q->where('transaction_type', 'keluar');
            });

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('item.category', function ($q2) use ($search) {
                        $q2->where('category_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($q3) use ($search) {
                        $q3->where('transaction_date', 'like', "%{$search}%");
                    });
            });
        }

        // Filter
        if ($request->filled('date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereDate('transaction_date', $request->date);
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
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
                    $query->orderBy('capital_price', 'asc');
                    break;

                case 'price_high':
                    $query->orderBy('capital_price', 'desc');
                    break;

                default:
                    $query->latest();
            }
        }

        $stockOuts = $query->paginate(12)->withQueryString();

        $items = Item::where('stock', '!=', 0)->get();
        $categories = Category::all();

        return view("pages.barang_keluar", compact('stockOuts', 'items', 'categories'));
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
                'item_id' => 'required|exists:items,id',
                'amount' => 'required|integer|min:1'
            ],
            [
                'item_id.required' => 'Barang wajib dipilih.',
                'item_id.exists' => 'Barang yang dipilih tidak valid.',

                'amount.required' => 'Jumlah barang wajib diisi.',
                'amount.integer' => 'Jumlah barang harus berupa angka bulat.',
                'amount.min' => 'Jumlah barang minimal 1.',
            ]
        );

        // Flash Session to Open Add Stock Out Modal Form if Error
        if ($validator->fails()) {
            return redirect()->route('barang_keluar.index')
                ->withErrors($validator, 'addStockOutItemError')
                ->withInput()
                ->with('modal', 'addStockOutItem');
        }

        $item = Item::findOrFail($request->item_id);

        // Flash Session to Stock Check and Open Add Stock Out Modal Form if Error
        if ($item->stock < $request->amount) {
            return redirect()->route('barang_keluar.index')
                ->withErrors([
                    'amount' => 'Jumlah barang melebihi stok yang tersedia.'
                ], 'addStockOutItemError')
                ->withInput()
                ->with('modal', 'addStockOutItem');
        }

        try {
            DB::transaction(function () use ($request, $item) {

                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'transaction_date' => now(),
                    'transaction_type' => 'keluar'
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'amount' => $request->amount,
                    'capital_price' => $item->capital_price,
                    'selling_price' => $item->selling_price,
                    'subtotal' => $request->amount * $item->selling_price
                ]);

                $item->decrement('stock', $request->amount);
            });

            return redirect()->route('barang_keluar.index')
                ->with('success', 'Barang keluar berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('barang_keluar.index')
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
