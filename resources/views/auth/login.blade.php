<x-guest-layout>
    <div class="px-5 py-10 sm:px-8">
        <div class="mx-auto w-full max-w-md">
            <header class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Welcome back</h1>
                <p class="mt-2 text-sm text-gray-600">Sign in with your account credentials.</p>
            </header>

            <section id="login-panel" class="login-panel border-t border-gray-200 pt-8">
                <h2 id="login-heading" class="text-xl font-semibold text-gray-900">Log in</h2>
                <p class="mt-1 text-sm text-gray-600">Enter your account details.</p>

                <x-auth-session-status class="mt-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-gray-600 underline hover:text-blue-700" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                        @endif
                    </div>

                    <x-primary-button class="w-full justify-center">{{ __('Log in') }}</x-primary-button>
                </form>
            </section>

            <p class="mt-8 text-center text-sm text-gray-600">
                Don't have an account?
                <a class="ms-1 font-semibold text-blue-700 underline hover:text-blue-900" href="{{ route('register') }}">Register</a>
            </p>
        </div>
    </div>

</x-guest-layout>
