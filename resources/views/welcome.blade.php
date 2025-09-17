<x-laravel-daisyui-starter::layout>
    <x-slot name="header">
        Welcome to Travel Order Management System
    </x-slot>

    <div class="hero min-h-screen bg-base-200">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <h1 class="text-5xl font-bold">TOMS</h1>
                <p class="py-6">“Simplify your travel order process with fast, secure, and role-based management tailored for your organization.”</p>
                <div class="flex gap-2 justify-center">
                    <a class="btn btn-primary" href="{{ route('login') }}">Get Started</a>
                    <button class="btn btn-ghost">Learn More</button>
                </div>

            </div>
        </div>
    </div>
</x-laravel-daisyui-starter::layout> 