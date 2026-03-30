@extends('components.layout')

@section('title', 'Data Barang')

@section('script')
    <script>
        function rupiahInput(initialNumber = '') {
            return {
                display: initialNumber ? 'Rp ' + Number(initialNumber).toLocaleString('id-ID') : '',
                number: initialNumber,

                formatRupiah() {
                    let value = this.display.replace(/[^0-9]/g, '');
                    this.number = value;

                    if (value) {
                        this.display = 'Rp ' + Number(value).toLocaleString('id-ID');
                    } else {
                        this.display = '';
                    }
                }
            }
        }
    </script>
@endsection

@section('header')
    <div class="flex flex-col w-full pb-3 border-b border-gray-300 gap-4 lg:flex-row">
        <h2 class="text-4xl font-semibold text-slate-900 w-full">Data Barang</h2>
        <form action="{{ route('barang.index') }}" method="GET" class="flex gap-2 w-full sm:max-w-80">
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

    <div x-data="{
        openAddItem: {{ session('modal') === 'addItem' ? 'true' : 'false' }},
        openEditItem: {{ session('modal') === 'editItem' ? 'true' : 'false' }},
    
        categories: @js($categories),
    
        categorySearch: '',
        categorySelectedId: '',
        openCategoryDropdown: false,
        categoryMode: '', // add|edit
        showNewCategory: false,
    
        resetCategoryState() {
            this.categorySearch = ''
            this.categorySelectedId = ''
            this.openCategoryDropdown = false
            this.showNewCategory = false
            this.categoryMode = ''
        },
    
        editItem: {
            id: '{{ old('id') }}',
            item_name: '{{ old('item_name') }}',
            category_id: '{{ old('category_id') }}',
            selling_price: '{{ old('selling_price') }}',
            capital_price: '{{ old('capital_price') }}',
            stock: '{{ old('stock') }}'
        },
    
        get filteredCategories() {
            if (this.categorySearch === '') return this.categories;
    
            return this.categories.filter(c =>
                c.category_name.toLowerCase()
                .includes(this.categorySearch.toLowerCase())
            );
        },
    
        selectCategory(category) {
            this.categorySelectedId = category.id;
            this.categorySearch = category.category_name;
            this.openCategoryDropdown = false;
            this.showNewCategory = false;
    
            if (this.categoryMode === 'edit') {
                this.editItem.category_id = category.id
            }
        },
    
        selectOther() {
            this.categorySelectedId = 'other';
            this.categorySearch = 'Lainnya';
            this.openCategoryDropdown = false;
            this.showNewCategory = true;
        },
    
        openEdit(item) {
            this.resetCategoryState();
            this.editItem = { ...item };
    
            const category = this.categories.find(
                c => c.id == item.category_id
            );
    
            this.categorySelectedId = item.category_id;
            this.categorySearch = category ? category.category_name : '';
    
            this.openEditItem = true;
        },
    
        closeAddModal() {
            this.openAddItem = false
            this.resetCategoryState()
        },
    
        closeEditModal() {
            this.openEditItem = false
            this.resetCategoryState()
        },
    }" class="relative flex flex-col gap-4">

        <div class="flex flex-col gap-4 md:flex-row md:flex-wrap w-full">

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('barang.index') }}" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap">

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
                            <option value="{{ $category->id }}"
                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>

                    <x-ri-arrow-down-s-line
                        class="w-6 h-6 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-black" />
                </div>

                {{-- Sort --}}
                <div class="relative w-full sm:w-40">
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

            {{-- Add Item Button --}}
            <button @click="resetCategoryState(); openAddItem = true"
                class="text-md p-2 w-full font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 border-2 border-lime-600 sm:max-w-40"
                id="add">Tambah</button>
        </div>

        {{-- Add Item Form Modal --}}
        <div x-show="openAddItem" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center" x-transition>
            <div @click.away="openAddItem = false"
                class="bg-white p-4 shadow w-full m-4 rounded-lg md:max-w-120 lg:max-w-lg">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold mb-4 w-full text-center">Tambah Barang</h2>

                    <button @click="closeAddModal()" class="text-red-600 cursor-pointer">
                        <x-ri-close-fill class="w-8 h-8" />
                    </button>
                </div>

                <form method="POST" action="{{ route('barang.store') }}" class="flex flex-col gap-6">
                    @csrf

                    {{-- Item Name --}}
                    <div>
                        <input type="text" name="item_name" placeholder="Nama Barang" required
                            value="{{ old('item_name') }}"
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        @error('item_name', 'addItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="relative">
                        <input type="hidden" name="category_id" :value="categorySelectedId">

                        <input type="text" x-model="categorySearch"
                            @focus="categoryMode = 'add';
        openCategoryDropdown = true;"
                            @input="openCategoryDropdown = true;
        categorySelectedId = '';
        showNewCategory = false;"
                            placeholder="Pilih Kategori"
                            class="block w-full rounded-md bg-white p-2 text-md border-2 border-gray-400 focus:border-lime-600 focus:outline-none">

                        {{-- Category Dropdown --}}
                        <div x-show="openCategoryDropdown"
                            @click.away="categoryMode = '';
        openCategoryDropdown = false;" x-transition
                            class="absolute z-50 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-52 overflow-y-auto">

                            <div @click="selectOther()"
                                class="px-3 py-2 border-b border-gray-200 hover:bg-gray-100 active:bg-gray-300 cursor-pointer text-lime-600 font-medium">
                                + Tambah Kategori Baru
                            </div>

                            <template x-for="category in filteredCategories" :key="category.id">
                                <div @click="selectCategory(category)"
                                    class="px-3 py-2 border-b last:border-b-0 border-gray-200 hover:bg-gray-100 active:bg-gray-300 cursor-pointer">
                                    <div class="font-medium" x-text="category.category_name"></div>
                                </div>
                            </template>
                        </div>

                        @error('category_id', 'addItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                        @error('new_category', 'addItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="showNewCategory" x-transition>
                        <input type="text" name="new_category" placeholder="Masukkan kategori baru"
                            class="block w-full rounded-md bg-transparent p-2 text-md border-2 border-gray-400 focus:border-lime-600 focus:outline-none">
                    </div>

                    {{-- Price --}}
                    <div x-data="rupiahInput({{ old('capital_price') }})">
                        <input type="text" x-model="display" @input="formatRupiah" placeholder="Harga Modal/Satuan"
                            required
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        <input type="hidden" name="capital_price" :value="number">

                        @error('capital_price', 'addItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="rupiahInput({{ old('selling_price') }})">
                        <input type="text" x-model="display" @input="formatRupiah" placeholder="Harga Jual/Satuan"
                            required
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        <input type="hidden" name="selling_price" :value="number">

                        @error('selling_price', 'addItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Stock --}}
                    <div>
                        <input type="number" name="stock" placeholder="Stok Barang" required
                            value="{{ old('stock') }}"
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        @error('stock', 'addItemError')
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

        {{-- Edit Item Form Modal --}}
        <div x-show="openEditItem" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center"
            x-transition>
            <div @click.away="openEditItem = false"
                class="bg-white p-4 shadow w-full m-4 rounded-lg md:max-w-120 lg:max-w-lg">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold mb-4 w-full text-center">Edit Barang</h2>
                    <button @click="closeEditModal()" class="text-red-600 cursor-pointer">
                        <x-ri-close-fill class="w-8 h-8" />
                    </button>
                </div>

                <form method="POST" :action="`{{ url('barang') }}/${editItem.id}`" class="flex flex-col gap-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" x-model="editItem.id">

                    {{-- Item Name --}}
                    <div>
                        <input type="text" name="item_name" placeholder="Nama Barang" required
                            x-model="editItem.item_name"
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        @error('item_name', 'editItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="relative w-full">
                        <input type="hidden" name="category_id" :value="categorySelectedId">
                        <input type="text" x-model="categorySearch"
                            @focus="categoryMode = 'edit';
        openCategoryDropdown = true;"
                            @input="openCategoryDropdown = true;
        categorySelectedId = '';"
                            placeholder="Pilih Kategori"
                            class="block w-full rounded-md bg-white p-2 text-md border-2 border-gray-400 focus:border-lime-600 focus:outline-none">

                        <div x-show="openCategoryDropdown"
                            @click.away="categoryMode = '';
        openCategoryDropdown = false;" x-transition
                            class="absolute z-50 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-52 overflow-y-auto">

                            <template x-for="category in filteredCategories" :key="category.id">
                                <div @click="selectCategory(category)"
                                    class="px-3 py-2 border-b last:border-b-0 border-gray-200 hover:bg-lime-100 cursor-pointer">
                                    <div class="font-medium" x-text="category.category_name"></div>
                                </div>
                            </template>

                            <div x-show="filteredCategories.length === 0" class="px-3 py-2 text-gray-400 text-sm">
                                Kategori tidak ditemukan
                            </div>

                        </div>

                        @error('category_id', 'editItemError')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Price --}}
                    <div x-data="rupiahInput(editItem.capital_price)" x-init="$watch('editItem.capital_price', value => {
                        display = value ? 'Rp ' + Number(value).toLocaleString('id-ID') : '';
                        number = value
                    })">
                        <input type="text" x-model="display" @input="formatRupiah" placeholder="Harga Modal/Satuan"
                            required
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        <input type="hidden" name="capital_price" :value="number">

                        @error('capital_price', 'editItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="rupiahInput(editItem.selling_price)" x-init="$watch('editItem.selling_price', value => {
                        display = value ? 'Rp ' + Number(value).toLocaleString('id-ID') : '';
                        number = value
                    })">
                        <input type="text" x-model="display" @input="formatRupiah" placeholder="Harga Jual/Satuan"
                            required
                            class="block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400">

                        <input type="hidden" name="selling_price" :value="number">

                        @error('selling_price', 'editItemError')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="text-md p-2 w-full font-medium rounded-md text-white bg-lime-600 cursor-pointer hover:brightness-110 duration-300 ">
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
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Modal</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Harga Jual</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Barang Masuk</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Barang Keluar</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Sisa Stok</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr class="font-normal">
                                        <td class="whitespace-nowrap px-6 py-4">{{ $item->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $item->item_name }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $item->category->category_name }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($item->capital_price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">Rp
                                            {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            {{ $item->total_masuk ?? 0 }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $item->total_keluar ?? 0 }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">{{ $item->stock }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 space-x-2"><button
                                                @click.prevent="openEdit({
                                                                    id: {{ $item->id }},
                                                                    item_name: '{{ addslashes($item->item_name) }}',
                                                                    category_id: '{{ $item->category_id }}',
                                                                    capital_price: '{{ $item->capital_price }}',
                                                                    selling_price: '{{ $item->selling_price }}',
                                                                    stock: '{{ $item->stock }}'
                                                                })"
                                                class="p-1 rounded-sm bg-lime-600 cursor-pointer hover:brightness-110 duration-300"><x-ri-edit-line
                                                    class="text-slate-50 w-6 h-6" /></button>
                                            <form method="POST" action="{{ route('barang.destroy', $item->id) }}"
                                                onsubmit="return confirm('Yakin ingin menghapus data?')"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="p-1 rounded-sm bg-red-700 cursor-pointer hover:brightness-110 duration-300"><x-ri-delete-bin-line
                                                        class="text-white w-6 h-6" /></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            {{ $items->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
