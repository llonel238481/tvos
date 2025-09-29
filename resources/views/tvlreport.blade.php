<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Blade Layout</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <p></p>
            <P></P>
            <P></P>
            <P></P>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="flex flex-1 max-w-7xl mx-auto w-full px-4 mt-6 gap-6 flex-col md:flex-row">
        <!-- Sidebar -->
        <aside class="bg-white shadow-md rounded-lg p-4 w-full md:w-1/4">
            <ul class="space-y-2">
                <li><a href="#" class="block p-2 rounded hover:bg-gray-100">Dashboard</a></li>
                <li><a href="#" class="block p-2 rounded hover:bg-gray-100">Users</a></li>
                <li><a href="#" class="block p-2 rounded hover:bg-gray-100">Settings</a></li>
            </ul>
        </aside>

        <!-- Content -->
        <main class="bg-white shadow-md rounded-lg p-6 flex-1">
            <h2 class="text-2xl font-semibold mb-4">Welcome to the Dashboard</h2>
            <p class="text-gray-600">This is a simple responsive layout in a single Blade file using Tailwind CSS.</p>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t py-4 text-center text-sm text-gray-500 mt-6">
        © {{ date('Y') }} My App. All rights reserved.
    </footer>

</body>
</html>
