@extends('components.layout')

@section('title', 'Barang Masuk')

@section('header')
    <div class="flex flex-col w-full pb-3 border-b border-gray-300 gap-4 lg:flex-row">
        <h2 class="text-4xl font-semibold text-slate-900 w-full">Barang Masuk</h2>
        <form action="{{ route('barang_masuk.index') }}" method="GET" class="flex gap-2 w-full sm:max-w-80">
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
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="border-2 border-green-600 px-2 py-3 rounded-md mb-4 flex gap-3 items-center sm:max-w-fit">
            <x-ri-checkbox-circle-line class="w-6 h-6 text-green-600" />
            <p class="text-green-600">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="border-2 border-red-600 px-2 py-3 rounded-md mb-4 flex gap-3 items-center sm:max-w-fit">
            <x-ri-error-warning-line class="w-6 h-6 text-red-600" />
            <p class="text-red-600">{{ session('error') }}</p>
        </div>
    @endif

    <div x-data="{ openAddItem: {{ session('modal') === 'addStockInItem' ? 'true' : 'false' }} }" class="relative flex flex-col gap-4 md:flex-row md:flex-wrap w-full">

        {{-- Filter Form --}}
        <form method="GET" action="" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap">

            {{-- Date --}}
            <input type="date" name="date"
                class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400 sm:w-40"
                value="{{ request('date') }}">

            {{-- Category --}}
            <div class="relative sm:w-40">
                <select name="category_id"
                    class="appearance-none block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">
                    <option value="" {{ request('category_id') ? '' : 'disabled selected' }}>
                        {{ request('category_id') ? 'Semua' : 'Kategori' }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
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

        {{-- Add Stock In Item Button --}}
        <button @click="openAddItem = true"
            class="text-md p-2 w-full font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 border-2 border-lime-600 sm:w-40"
            id="add">Tambah</button>

        {{-- Add Stock In Item Form Modal --}}
        <div x-show="openAddItem" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center" x-transition>
            <div @click.away="openAddItem = false"
                class="bg-white p-4 shadow w-full m-4 rounded-lg md:max-w-120 lg:max-w-lg">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold mb-4 w-full text-center">Tambah Barang Masuk</h2>
                    <button @click="openAddItem = false" class=" text-red-600 cursor-pointer">
                        <x-ri-close-fill class="w-8 h-8" />
                    </button>
                </div>

                <form method="POST" action="{{ route('barang_masuk.store') }}" class="flex flex-col gap-6">
                    @csrf
                    {{-- Item Select --}}
                    <div x-data="{
                        openItem: false,
                        searchItem: '',
                        selectedIdItem: '',
                        items: @js($items),
                    
                        get filteredItems() {
                            if (this.searchItem === '') return this.items
                            return this.items.filter(item =>
                                item.item_name.toLowerCase().includes(this.searchItem.toLowerCase())
                            )
                        },
                    
                        selectItem(item) {
                            this.selectedIdItem = item.id
                            this.searchItem = item.item_name
                            this.openItem = false
                        }
                    }" class="relative w-full">
                        <input type="hidden" name="item_id" :value="selectedIdItem">

                        <input type="text" x-model="searchItem" @focus="openItem = true" @input="openItem = true"
                            placeholder="Cari Barang"
                            class="block w-full rounded-md bg-white p-2 text-md border-2 border-gray-400 focus:border-lime-600 focus:outline-none">

                        <div x-show="openItem" x-transition @click.away="openItem = false"
                            class="absolute z-50 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-52 overflow-y-auto">

                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="selectItem(item)"
                                    class="px-3 py-2 border-b last:border-b-0 border-gray-200 hover:bg-gray-100 active:bg-gray-300 cursor-pointer">
                                    <div class="font-medium" x-text="item.item_name"></div>
                                    <div class="text-sm text-gray-500">
                                        Stok: <span x-text="item.stock"></span>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredItems.length === 0" class="px-3 py-2 text-gray-400 text-sm">
                                Barang tidak ditemukan
                            </div>
                        </div>

                        @error('item_id', 'addStockInItemError')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount of Stock --}}
                    <div>
                        <input type="number" name="amount" placeholder="Jumlah Stok" required
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        @error('amount', 'addStockInItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="text-md p-2 w-full font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 border-2 border-lime-600">
                        Simpan
                    </button>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="space-y-4 w-full mt-2">
            <div class="overflow-x-auto w-full border border-gray-300 rounded-md">
                <div class="inline-block min-w-full">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-left text-sm font-light text-surface">
                            <thead class="border-b border-neutral-300 font-medium">
                                <tr>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Nama Barang</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Kategori</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Modal/Satuan</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockIns as $stockIn)
                                    <tr class="font-normal">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockIn->transaction->created_at->format('d/m/Y') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockIn->item_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockIn->item?->category?->category_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($stockIn->capital_price, 0, ',', '.') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $stockIn->amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            {{ $stockIns->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
