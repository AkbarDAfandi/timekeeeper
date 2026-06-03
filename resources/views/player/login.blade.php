<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Node Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen antialiased">
    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-sm ring-1 ring-gray-950/5">
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold tracking-tight text-gray-950">Authenticate Player</h2>
            <p class="mt-2 text-sm text-gray-500">Sign in to access the playback execution engine.</p>
        </div>

        <form method="POST" action="{{ url('/source-login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-950">Email address</label>
                <div class="mt-2">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="block w-full rounded-lg border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6 px-3">
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-danger-600 text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium leading-6 text-gray-950">Password</label>
                <div class="mt-2">
                    <input id="password" name="password" type="password" required class="block w-full rounded-lg border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6 px-3">
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-danger-600 text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</body>
</html>
