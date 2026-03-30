<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $stockIn = TransactionDetail::whereHas('transaction', function ($query) use ($request) {
            $query->where('transaction_type', "masuk");

            if ($request->filled('date')) {
                $query->whereDate('transaction_date', $request->date);
            }

            if ($request->filled('month')) {
                $query->whereMonth('transaction_date', \Carbon\Carbon::parse($request->month)->month)
                    ->whereYear('transaction_date', \Carbon\Carbon::parse($request->month)->year);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            }
        })->sum('subtotal');

        $stockOut = TransactionDetail::whereHas('transaction', function ($query) use ($request) {
            $query->where('transaction_type', "keluar");

            if ($request->filled('date')) {
                $query->whereDate('transaction_date', $request->date);
            }

            if ($request->filled('month')) {
                $query->whereMonth('transaction_date', \Carbon\Carbon::parse($request->month)->month)
                    ->whereYear('transaction_date', \Carbon\Carbon::parse($request->month)->year);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            }
        })->sum('subtotal');

        $profit = TransactionDetail::whereHas('transaction', function ($query) use ($request) {
            $query->where('transaction_type', 'keluar');

            if ($request->filled('date')) {
                $query->whereDate('transaction_date', $request->date);
            }

            if ($request->filled('month')) {
                $query->whereMonth('transaction_date', \Carbon\Carbon::parse($request->month)->month)
                    ->whereYear('transaction_date', \Carbon\Carbon::parse($request->month)->year);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            }
        })
            ->get()
            ->sum(function ($detail) {
                return ($detail->selling_price - $detail->capital_price) * $detail->amount;
            });

        $items = Item::with('category')
            ->withSum(
                ['transactiondetails as total_masuk' => function ($query) {
                    $query->whereHas('transaction', function ($query2) {
                        $query2->where('transaction_type', 'masuk');
                    });
                }],
                'amount'
            )
            ->withSum(
                ['transactiondetails as total_keluar' => function ($query) {
                    $query->whereHas('transaction', function ($query2) {
                        $query2->where('transaction_type', 'keluar');
                    });
                }],
                'amount'
            )->orderBy('stock', 'asc')->get();

        $outOfStockItems = $items->where('stock', 0);
        $lowStockItems = $items->whereBetween('stock', [1, 9]);

        return view('pages.dashboard', compact('stockIn', 'stockOut', 'profit', 'outOfStockItems', 'lowStockItems'));
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
