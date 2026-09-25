<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-8">
        <div class="w-full max-w-4xl bg-white shadow-lg rounded-lg overflow-hidden grid grid-cols-1 md:grid-cols-2">
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold">Victor Bernal Provincial High School</h2>
                    <p class="text-sm text-gray-600">Password Reset</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <a class="underline text-sm text-gray-600 hover:text-blue-700" href="{{ route('login') }}">Back to login</a>
                        <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="hidden md:flex items-center justify-center bg-gradient-to-tr from-blue-50 to-white">
                <div class="text-center px-6">
                    <h3 class="text-3xl font-extrabold text-blue-700">Victor Bernal</h3>
                    <p class="mt-2 text-gray-700">We'll email a reset link</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
