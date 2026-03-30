@extends('components.layout')

@section('title', 'Laporan')

@section('header')
    <div class="flex flex-col w-full pb-3 border-b border-gray-300 gap-4 lg:flex-row">
        <h2 class="text-4xl font-semibold text-slate-900 w-full">Laporan</h2>
        <form action="{{ route('laporan.index') }}" method="GET" class="flex gap-2 w-full sm:max-w-80">
            <input id="search" type="text" name="search" placeholder="Pencarian"
                class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400"
                value="{{ request('search') }}" />

            <button type="submit"
                class="text-md px-6 font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 w-fit border-2 border-lime-600">
                Cari
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:flex-wrap w-full">

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('laporan.index') }}"
            class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end" x-data="{
                filterType: '{{ request('date') ? 'date' : (request('month') ? 'month' : (request('start_date') ? 'range' : '')) }}',
                date: '{{ request('date') }}',
                monthDate: '{{ request('month') }}',
                startDate: '{{ request('start_date') }}',
                endDate: '{{ request('end_date') }}'
            }"a>

            {{-- Date Option --}}
            <div class="relative">
                <select x-model="filterType"
                    class="appearance-none block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400 sm:w-40">
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
                :disabled="filterType !== 'date'"
                class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400 sm:w-40">

            {{-- Monthly --}}
            <input type="month" x-model="monthDate" name="month" x-show="filterType === 'month'" x-transition
                :disabled="filterType !== 'month'"
                class="block w-full rounded-md p-2 border-2 border-gray-400 focus:border-lime-600 focus:outline-none sm:w-40">

            {{-- Range --}}
            <div x-show="filterType === 'range'" x-transition class="flex flex-col gap-4 sm:flex-row">
                <div class="flex flex-col gap-1.5">
                    <p class="text-sm text-slate-900 font-medium">Tanggal Mulai</p>
                    <input type="date" name="start_date" x-model="startDate" :disabled="filterType !== 'range'"
                        class="block w-full rounded-md p-2 border-2 border-gray-400 focus:border-lime-600 focus:outline-none">
                </div>

                <div class="flex flex-col gap-1.5">
                    <p class="text-sm text-slate-900 font-medium">Tanggal Berakhir</p>
                    <input type="date" name="end_date" x-model="endDate" :min="startDate"
                        :disabled="filterType !== 'range'"
                        class="block w-full rounded-md p-2 border-2 border-gray-400 focus:border-lime-600 focus:outline-none">
                </div>
            </div>

            {{-- Transaction Type --}}
            <div class="relative sm:w-40">
                <select name="transaction_type"
                    class="appearance-none block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">
                    <option value="" {{ request('transaction_type') ? '' : 'disabled selected' }}>
                        {{ request('transaction_type') ? 'Semua' : 'Tipe Transaksi' }}</option>
                    <option value="masuk" {{ request('transaction_type') == 'masuk' ? 'selected' : '' }}>
                        Masuk
                    </option>
                    <option value="keluar" {{ request('transaction_type') == 'keluar' ? 'selected' : '' }}>
                        Keluar
                    </option>
                </select>

                <x-ri-arrow-down-s-line
                    class="w-6 h-6 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-black" />
            </div>

            {{-- Category --}}
            <div class="relative sm:w-40">
                <select name="category"
                    class="appearance-none block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">
                    <option value="" {{ request('category') ? '' : 'disabled selected' }}>
                        {{ request('category') ? 'Semua' : 'Kategori' }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>

                <x-ri-arrow-down-s-line
                    class="w-6 h-6 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-black" />
            </div>

            {{-- Sort --}}
            <div class="relative sm:w-40">
                <select name="sort"
                    class="appearance-none block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">
                    <option value="" disabled selected>Urutkan</option>
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga Termurah
                    </option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga Termahal
                    </option>
                </select>
                <x-ri-arrow-down-s-line
                    class="w-6 h-6 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-black" />
            </div>

            <button type="submit"
                class="text-md p-2 w-full font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 border-2 border-lime-600 sm:w-40"
                id="filter">Filter</button>
        </form>

        {{-- Print Item Button --}}
        <a href="{{ route('laporan.print', request()->query()) }}"
            class="text-center text-md p-2 w-full font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 border-2 border-lime-600 sm:w-40 self-end"
            id="print">Cetak</a>

        {{-- Data Barang Table --}}
        <div class="space-y-6 w-full">
            <div class="overflow-x-auto w-full border border-gray-300 rounded-md">
                <div class="inline-block min-w-full">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-left text-sm font-light text-surface">
                            <thead class="border-b border-neutral-300 font-medium">
                                <tr>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Nama Barang</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Kategori</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Modal</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Jual</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Tipe Transaksi</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockInAndOuts as $stockInAndOut)
                                    <tr class="font-normal">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockInAndOut->transaction->created_at->format('d/m/Y') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockInAndOut->item_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockInAndOut->item?->category?->category_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($stockInAndOut->capital_price, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($stockInAndOut->selling_price, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ ucwords($stockInAndOut->transaction?->transaction_type) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockInAndOut->amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            {{ $stockInAndOuts->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
