@extends('components.auth-layout')

@section('title', 'Masuk')

@section('content')
    <div class="sm:flex sm:items-center sm:justify-center lg:justify-between p-6 lg:p-0 w-full">
        <img src="{{ asset('images/large_banner.jpeg') }}" alt="banner-image"
            class="hidden xl:block lg:max-w-3xl lg:w-full lg:h-screen object-cover lg:brightness-50">

        <div class="w-full flex justify-center">
            <form method="POST" class="flex flex-col gap-8 w-full sm:max-w-sm md:max-w-md sm:p-7 sm:shadow-md rounded-md">
                @csrf
                <img src="{{ asset('images/medium_logo.png') }}" alt="logo-image"
                    class="h-22 object-fill sm:max-w-lg mx-auto block">

                <div class="flex flex-col gap-6">
                    <div>
                        <input type="text" name="username" placeholder="Username" value="{{ old('username') }}"
                            class=" @error('username') border-red-600 @enderror block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400" />
                        @error('username')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <input type="password" name="password" placeholder="Password"
                            class=" @error('password') border-red-600 @enderror block w-full rounded-md bg-transparent p-2 text-md placeholder:text-gray-400 focus:outline-none border-2 focus:border-lime-600 border-gray-400" />
                        @error('password')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="text-md p-2 w-full font-medium text-white rounded-md bg-lime-600 hover:brightness-110 duration-300">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
