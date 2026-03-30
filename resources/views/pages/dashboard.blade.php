@extends('components.layout')

@section('title', 'Dashboard')

@section('header')
    <div class="w-full gap-1.5 pb-3 border-b border-gray-300">
        <h2 class="text-4xl font-semibold text-slate-900">Dashboard</h2>
    </div>
@endsection

@section('content')
    {{-- Summary --}}
    <div class="space-y-6 w-full">
        <div class="flex flex-col gap-4 justify-between">
            <h3 class="text-2xl text-slate-900 font-semibold">Total Transaksi</h3>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.index') }}" class="flex flex-col gap-4 sm:flex-row"
                x-data="{
                    filterType: '{{ request('date') ? 'date' : (request('month') ? 'month' : (request('start_date') ? 'range' : '')) }}',
                    date: '{{ request('date') }}',
                    monthDate: '{{ request('month') }}',
                    startDate: '{{ request('start_date') }}',
                    endDate: '{{ request('end_date') }}'
                }"a>

                {{-- Date Option --}}
                <div class="relative sm:w-full sm:max-w-40 sm:self-end">
                    <select x-model="filterType"
                        @change="if($event.target.value === 'all') window.location='{{ route('dashboard.index') }}'"
                        class="appearance-none block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">
                        <option value="" disabled selected>Pilih Tanggal</option>
                        @if (request('date') || request('month') || request('start_date'))
                            <option value="all">Semua</option>
                        @endif
                        <option value="date">Harian</option>
                        <option value="month">Bulanan</option>
                        <option value="range">Range</option>
                    </select>

                    <x-ri-arrow-down-s-line
                        class="w-6 h-6 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-black" />
                </div>

                {{-- Date --}}
                <input type="date" name="date" x-show="filterType === 'date'" x-transition
                    value="{{ request('date') }}" :disabled="filterType !== 'date'"
                    class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400 sm:max-w-40"
                    @change="$el.form.submit()">

                {{-- Monthly --}}
                <input type="month" x-model="monthDate" name="month" x-show="filterType === 'month'" x-transition
                    value="{{ request('month') }}" :disabled="filterType !== 'month'"
                    class="block w-full rounded-md p-2 border-2 border-gray-400 focus:border-lime-600 focus:outline-none sm:max-w-40"
                    @change="$el.form.submit()">

                {{-- Range --}}
                <div x-show="filterType === 'range'" x-transition class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex flex-col gap-1.5">
                        <p class="text-sm text-slate-900 font-medium">Tanggal Mulai</p>
                        <input type="date" name="start_date" x-model="startDate" value="{{ request('start_date') }}"
                            :disabled="filterType !== 'range'"
                            class="block w-full rounded-md p-2 border-2 border-gray-400 focus:border-lime-600 focus:outline-none">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <p class="text-sm text-slate-900 font-medium">Tanggal Berakhir</p>
                        <input type="date" name="end_date" x-model="endDate" @change="$el.form.submit()"
                            :min="startDate" value="{{ request('end_date') }}" :disabled="filterType !== 'range'"
                            class="block w-full rounded-md p-2 border-2 border-gray-400 focus:border-lime-600 focus:outline-none">
                    </div>
                </div>
            </form>
        </div>

        <div class="flex w-full gap-6 flex-col lg:flex-row">
            <div class="w-full border border-gray-300 p-6 rounded-md space-y-2">
                <h4 class="text-lg font-semibold text-slate-900">Keuntungan</h4>
                <p class="text-3xl font-medium text-lime-600">{{ 'Rp ' . number_format($profit, 0, ',', '.') }}</p>
            </div>

            <div class="w-full border border-gray-300 p-6 rounded-md space-y-2">
                <h4 class="text-lg font-semibold  text-slate-900">Barang Masuk</h4>
                <p class="text-3xl font-medium text-lime-600">{{ 'Rp ' . number_format($stockIn, 0, ',', '.') }}</p>
            </div>

            <div class="w-full border border-gray-300 p-6 rounded-md space-y-2">
                <h4 class="text-lg font-semibold text-slate-900">Barang Keluar</h4>
                <p class="text-3xl font-medium text-lime-600">{{ 'Rp ' . number_format($stockOut, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Notification Table --}}
    @if ($lowStockItems->count())
        <div class="space-y-4 w-full">
            <div class="flex justify-between">
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <div class="w-2 h-2 bg-amber-600 animate-ping rounded-full translate-y-1.5"></div>
                        <h3 class="text-2xl block text-slate-900 font-semibold">Stok Menipis</h3>
                    </div>
                    <p class="text-sm text-gray-700 leading-5">Menampilkan <span
                            class="font-medium">{{ $lowStockItems->count() }}</span> barang</p>
                </div>
            </div>

            <div class="overflow-x-auto w-full border border-gray-300 rounded-md">
                <div class="inline-block min-w-full">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-left text-sm font-light text-surface">
                            <thead class="border-b border-neutral-300 font-medium">
                                <tr>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Nama Barang</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Kategori</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Modal</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Jual</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Barang Masuk</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Barang Keluar</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockItems as $lowStockItem)
                                    <tr class="font-normal">
                                        <td class="whitespace-nowrap px-6 py-4">{{ $lowStockItem->item_name }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $lowStockItem->category->category_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($lowStockItem->capital_price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($lowStockItem->selling_price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $lowStockItem->total_masuk ?? 0 }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $lowStockItem->total_keluar ?? 0 }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $lowStockItem->stock }}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    @endif

    @if ($outOfStockItems->count())
        <div class="space-y-4 w-full">
            <div class="flex justify-between">
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <div class="w-2 h-2 bg-red-600 animate-ping rounded-full translate-y-1.5"></div>
                        <h3 class="text-2xl block text-slate-900 font-semibold">Stok Habis</h3>
                    </div>
                    <p class="text-sm text-gray-700 leading-5">Menampilkan <span
                            class="font-medium">{{ $outOfStockItems->count() }}</span>
                        barang</p>
                </div>
            </div>

            <div class="overflow-x-auto w-full border border-gray-300 rounded-md">
                <div class="inline-block min-w-full">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-left text-sm font-light text-surface">
                            <thead class="border-b border-neutral-300 font-medium">
                                <tr>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Nama Barang</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Kategori</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Modal</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Jual</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Barang Masuk</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Barang Keluar</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outOfStockItems as $outOfStockItem)
                                    <tr class="font-normal">
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $outOfStockItem->item_name }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $outOfStockItem->category->category_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($outOfStockItem->capital_price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($outOfStockItem->selling_price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $outOfStockItem->total_masuk ?? 0 }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $outOfStockItem->total_keluar ?? 0 }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $outOfStockItem->stock }}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    @endif
    </div>
@endsection
