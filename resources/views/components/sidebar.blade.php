<aside x-data="{ open: false }" x-cloak :class="open
    ?
    'translate-y-0' :
    '-translate-y-full'"
    class="p-4 fixed lg:absolute bg-white gap-12 w-full h-screen flex flex-col justify-between transition-transform duration-1000 shadow-sm z-10 lg:z-0 lg:shadow-none lg:border-r lg:border-gray-300 lg:translate-y-0 lg:translate-x-0 lg:w-64">

    <div class="flex flex-col gap-6 w-full">
        <div class="flex justify-between items-start">
            <img src="{{ asset('images/medium_logo.png') }}" alt="logo-image" class="h-16 object-fill">

            {{-- Smartphone Button --}}
            <button
                :class="open ? '' :
                    'top-full right-0 fixed bg-white border border-gray-300 shadow-sm rounded-bl-md p-1.5 lg:hidden'"
                @click="open = !open" class="text-slate-700 ">

                <template x-if="open">
                    <x-ri-close-fill class="w-12 h-12" />
                </template>

                <template x-if="!open">
                    <x-ri-arrow-down-s-line class="w-12 h-12" />
                </template>
            </button>
        </div>

        <div class="flex gap-4 items-center w-full">
            <x-ri-user-fill
                class="w-full max-w-14 text-slate-400 border border-slate-300 rounded-full p-2 lg:max-w-12" />
            <div class="w-full overflow-hidden">
                <p class="text-lg font-semibold text-slate-900 truncate w-full lg:text-[1.1rem]">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-lg font-sans lg:text-[1.1rem]">{{ ucwords(auth()->user()->role) }}</p>
            </div>
        </div>
    </div>

    {{-- Navigation Menu --}}
    <div class="flex flex-col gap-5 w-full h-full lg:justify-start lg:h-fit">
        @foreach ($itemsNav as $itemNav)
            <a href="{{ $itemNav['href'] }}"
                class="px-2 py-3 text-lg flex items-center gap-4 rounded-md border {{ request()->routeIs($itemNav['route'] . '.index') ? 'border-lime-600' : 'border-slate-300 hover:bg-slate-100 text-slate-900' }}">
                {{ svg($itemNav['icon'], 'w-10 h-10 text-lime-600 lg:w-8 lg:h-8') }}
                <span class="text-slate-900 font-medium text-lg lg:text-[1.1rem]">
                    {{ $itemNav['name'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Logout Option --}}
    <form method="POST" action="{{ route('logout') }}"" class="lg:h-full">
        @csrf
        <button type="submit"
            class="w-full px-2 py-3 font-semibold hover:bg-slate-100 text-lg flex items-center gap-2 rounded-md border border-slate-300 text-red-400 cursor-pointer lg:self-start">
            <x-ri-logout-box-line class="w-10 h-10 text-red-700 lg:w-8 lg:h-8" />
            <span class="text-slate-900 font-medium text-lg lg:text-[1.1rem]">
                Keluar
            </span>
        </button>
    </form>

</aside>
