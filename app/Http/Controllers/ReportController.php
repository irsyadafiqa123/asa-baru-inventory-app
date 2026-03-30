<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Date;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TransactionDetail::with(['transaction', 'item.category']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('item.category', function ($q2) use ($search) {
                        $q2->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter
        if ($request->filled('transaction_type')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->where('transaction_type', $request->transaction_type);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('item.category', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereDate('transaction_date', $request->date);
            });
        }

        if ($request->filled('month')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereMonth('transaction_date', \Carbon\Carbon::parse($request->month)->month)
                    ->whereYear('transaction_date', \Carbon\Carbon::parse($request->year)->year);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
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
                    $query->orderBy('selling_price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('selling_price', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        $stockInAndOuts = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return view("pages.laporan", compact('stockInAndOuts', 'categories'));
    }

    // Print Controller
    public function print(Request $request)
    {
        // Filter
        $query = TransactionDetail::with(['transaction', 'item.category']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('item.category', function ($q2) use ($search) {
                        $q2->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereDate('transaction_date', $request->date);
            });
        }

        if ($request->filled('month')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereMonth('transaction_date', \Carbon\Carbon::parse($request->month)->month)
                    ->whereYear('transaction_date', \Carbon\Carbon::parse($request->year)->year);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            });
        }

        if ($request->filled('transaction_type')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->where('transaction_type', $request->transaction_type);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('item.category', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        $stockInAndOuts = $query->get()->toArray();

        // Summary Calculate
        $stockIn = collect($stockInAndOuts)
            ->where('transaction.transaction_type', 'masuk')
            ->sum('subtotal');

        $stockOut = collect($stockInAndOuts)
            ->where('transaction.transaction_type', 'keluar')
            ->sum('subtotal');

        $profit = collect($stockInAndOuts)
            ->where('transaction.transaction_type', 'keluar')
            ->sum(function ($detail) {
                return ($detail['selling_price'] - $detail['capital_price']) * $detail['amount'];
            });

        $data = ['stockInAndOuts' => $stockInAndOuts, 'stockIn' => $stockIn, 'stockOut' => $stockOut, 'profit' => $profit];
        $pdf = Pdf::loadView('pages.cetak_laporan', $data)->setPaper('A4', 'portrait');

        $date = \Carbon\Carbon::now()->format('Y-m-d');
        return $pdf->stream('Laporan Transaksi Toko Plastik Asa Baru ' . $date . '.pdf');
        // return $pdf->download('Laporan Transaksi Toko Plastik Asa Baru ' . $date . '.pdf');
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
        //
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
