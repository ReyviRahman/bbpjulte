<x-guest-layout>
    <!-- Custom Styles for Login Form -->
    <style>
        .input-with-icon {
            position: relative;
        }
        .input-with-icon svg {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: #9ca3af; /* gray-400 */
        }
        .input-with-icon input {
            padding-left: 32px !important;
            border: none;
            border-bottom: 1px solid #d1d5db; /* gray-300 */
            border-radius: 0;
            box-shadow: none !important;
        }
        .input-with-icon input:focus {
            border-bottom-color: #3b82f6; /* blue-500 */
            ring: 0;
        }
    </style>

    <!-- Logo and Title -->
    <div class="flex flex-col items-center mb-6">
        <h1 class="text-xl font-semibold text-blue-700">Unit Layanan Terpadu</h1>
        <p class="text-sm text-gray-500 text-center">Balai bahasa Provinsi Jambi</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address or NIP -->
        <div class="mb-4">
            <x-input-label for="login" value="Email atau NIP" class="sr-only" />
            <div class="input-with-icon">
                <!-- User Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <x-text-input id="login" class="block w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Email atau NIP" />
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="sr-only" />
            <div class="input-with-icon">
                <!-- Lock Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <x-text-input id="password" class="block w-full"
                              type="password"
                              name="password"
                              required autocomplete="current-password"
                              placeholder="Password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-blue-600 hover:text-blue-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Lupa Password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center !py-3">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
