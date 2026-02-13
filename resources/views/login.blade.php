<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white w-full max-w-sm p-6 rounded-lg shadow">
        <h1 class="text-xl font-semibold mb-4 text-center">Login Admin</h1>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-700 bg-red-100 p-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf

            <label class="block text-sm font-medium mb-1">Username</label>
            <input name="username" value="{{ old('username') }}" class="w-full border rounded px-3 py-2 mb-3" required>

            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-4" required>

            <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
        </form>
    </div>
</body>

</html>
