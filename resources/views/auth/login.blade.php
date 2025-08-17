<x-guest-layout>
    <div x-data="{ showPassword: false }">
        <div class="text-center mb-6 animate-fade-in-down">
            <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
            <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- User Input -->
            <div class="space-y-2" x-data="{ showUserInput: false }" x-init="setTimeout(() => showUserInput = true, 100)">
                <x-input-label for="user" :value="__('Username/Roll Number')" />
                <x-text-input id="user" class="block w-full transition-all duration-300" x-bind:class="@json(['opacity-0 translate-y-2' => '!showUserInput', 'opacity-100 translate-y-0' => 'showUserInput'])" type="text" name="user" :value="old('user')" required autofocus autocomplete="user" placeholder="Enter your username or roll number" />
                <x-input-error :messages="$errors->get('user')" class="mt-1" />
                <p class="text-xs text-gray-500">
                    Students: Enter your roll number (e.g., 1234567890) | Teachers: Enter your username (e.g., john.doe)
                </p>
            </div>

            <!-- Password Input -->
            <div class="mt-4 space-y-2" x-data="{ showPasswordInput: false }" x-init="setTimeout(() => showPasswordInput = true, 200)">
                <x-input-label for="pass" :value="__('Password')" />
                <div class="relative">
                    <x-text-input id="pass" class="block w-full pr-10 transition-all duration-300" x-bind:class="@json(['opacity-0 translate-y-2' => '!showPasswordInput', 'opacity-100 translate-y-0' => 'showPasswordInput'])" x-bind:type="showPassword ? 'text' : 'password'" name="pass" required autocomplete="current-password" />
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" @click="showPassword = !showPassword">
                        <svg x-show="!showPassword" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-cloak x-show="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path x-cloak x-show="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-cloak x-show="showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('pass')" class="mt-1" />
            </div>

            <!-- Remember Me and Forgot Password -->
            <div class="mt-4 flex items-center justify-between" x-data="{ showRememberMe: false }" x-init="setTimeout(() => showRememberMe = true, 300)">
                <label for="remember" class="inline-flex items-center transition-all duration-300" x-bind:class="@json(['opacity-0 translate-y-2' => '!showRememberMe', 'opacity-100 translate-y-0' => 'showRememberMe'])">
                    <input id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                                    <a class="text-sm text-indigo-600 hover:text-indigo-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300" x-bind:class="@json(['opacity-0 translate-y-2' => '!showRememberMe', 'opacity-100 translate-y-0' => 'showRememberMe'])" href="{{ route('password.request') }}">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
            </div>

            <!-- Login Button -->
            <div class="mt-6" x-data="{ showLoginButton: false }" x-init="setTimeout(() => showLoginButton = true, 400)">
                <x-primary-button class="w-full flex justify-center transition-all duration-300" x-bind:class="@json(['opacity-0 translate-y-2' => '!showLoginButton', 'opacity-100 translate-y-0' => 'showLoginButton'])">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            <!-- Login Type Info -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg shadow-sm transition-all duration-300 hover:shadow-md" x-data="{ showInfo: false }" x-init="setTimeout(() => showInfo = true, 500)">
                <div class="flex transition-all duration-300" x-bind:class="@json(['opacity-0 translate-y-2' => '!showInfo', 'opacity-100 translate-y-0' => 'showInfo'])">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Auto-Detection</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>The system automatically detects whether you're a student or teacher based on your input format.</p>
                            <div class="mt-2">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li><strong>Students:</strong> Long numeric roll numbers (10+ digits)</li>
                                    <li><strong>Teachers:</strong> Alphanumeric usernames or short numeric IDs (1-6 digits)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
