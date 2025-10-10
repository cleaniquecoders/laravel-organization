<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Organization - Workbench</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Laravel Organization</h1>
                <p class="text-gray-600 mb-8">Workbench Testing Environment</p>

                <div class="space-y-4">
                    <a href="{{ route('login') }}"
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                        Login
                    </a>

                    <a href="{{ route('register') }}"
                       class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                        Register
                    </a>

                    <div class="mt-8 pt-6 border-t border-gray-300">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Test Endpoints</h3>
                        <div class="space-y-2 text-sm">
                            <a href="/test-organization" class="block text-blue-600 hover:text-blue-800">/test-organization - Quick organization test</a>
                            <a href="/test-livewire" class="block text-blue-600 hover:text-blue-800">/test-livewire - Livewire components test</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
