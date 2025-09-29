<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ config('laravel-daisyui-starter.theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="hero min-h-screen">
        <div class="hero-content flex-col lg:flex-row-reverse">

            <!-- Left text -->
            <div class="flex flex-col lg:flex-row items-center lg:items-start lg:gap-8">

                <!-- Text Section -->
                <div class="text-center lg:text-left max-w-md">
                    <h1 class="text-5xl font-bold">Login now!</h1>
                    <p class="py-6">Welcome back! Please enter your credentials to access your account.</p>
                </div>
            </div>

            <!-- Login Card -->
            <div class="card flex-shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
                <div class="card-body">

                    <!-- Theme toggle -->
                    <div class="flex justify-end ">
                        <label class="swap swap-rotate">
                            <input type="checkbox" class="theme-controller" value="dark" />
                            <svg class="swap-on fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/>
                            </svg>
                            <svg class="swap-off fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/>
                            </svg>
                        </label>
                    </div>

                    <!-- Image Section -->
                    <div class="flex flex-col items-center mb-6">
                        <img 
                            src="/img/csulogo.png" 
                            alt="CSU Logo" 
                            class="w-12 sm:w-16 md:w-20 h-auto max-h-12 sm:max-h-16 md:max-h-20 object-contain rounded-lg"
                        >
                        <!-- Subheading -->
                        <span class="text-lg sm:text-xl md:text-2xl font-semibold mt-2 text-center text-base-content">
                            Cagayan State University - Lasam Campus
                        </span>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <!-- Email -->
                        <div class="form-control">
                            <label class="label"><span class="label-text">Email</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered" required autofocus autocomplete="username" />
                            @error('email')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-control">
                            <label class="label"><span class="label-text">Password</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="login-password" class="input input-bordered w-full pr-10" required autocomplete="current-password" />
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="togglePassword('login-password', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <!-- Login Button -->
                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                // Optionally change icon to indicate "visible"
                button.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.19-3.498M6.1 6.1L17.9 17.9M9.88 9.88a3 3 0 104.24 4.24" />
                    </svg>
                `;
            } else {
                input.type = "password";
                // Change back to original icon
                button.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                `;
            }
        }
    </script>
    
    <script>
        // Theme switcher
        document.addEventListener('DOMContentLoaded', function() {
            const themeControllers = document.querySelectorAll('.theme-controller');

            // Load saved theme from localStorage
            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', savedTheme);
            themeControllers.forEach(controller => controller.checked = savedTheme === 'dark');

            // Toggle theme on change
            themeControllers.forEach(controller => {
                controller.addEventListener('change', e => {
                    const theme = e.target.checked ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', theme);
                    localStorage.setItem('theme', theme);
                    themeControllers.forEach(c => c.checked = e.target.checked);
                });
            });
        });
    </script>
</body>
</html>
