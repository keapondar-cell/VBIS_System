<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-8">
        <div class="w-full max-w-4xl bg-white shadow-lg rounded-lg overflow-hidden grid grid-cols-1 md:grid-cols-2">
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold">Victor Bernal Provincial High School</h2>
                    <p class="text-sm text-gray-600">Victor Bernal Inventory System — Register</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Register as')" />
                        <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <option value="teacher" {{ old('role')=='teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="property_custodian" {{ old('role')=='property_custodian' ? 'selected' : '' }}>Property Custodian</option>
                            @if(!empty($allowAdmin) && $allowAdmin)
                                <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
                            @endif
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        @unless(!empty($allowAdmin) && $allowAdmin)
                            <p class="text-sm text-gray-500 mt-1">To register an Admin account, please ask an existing Admin to create it for you.</p>
                        @endunless
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <a class="underline text-sm text-gray-600 hover:text-blue-700" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
                        <x-primary-button class="ms-4">{{ __('Register') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="hidden md:flex items-center justify-center bg-gradient-to-tr from-blue-50 to-white">
                <div class="text-center px-6">
                    <h3 class="text-3xl font-extrabold text-blue-700">Victor Bernal</h3>
                    <p class="mt-2 text-gray-700">Create your account</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
