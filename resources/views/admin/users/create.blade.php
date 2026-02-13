@extends('admin.layout.index')

@section('title', 'Tambah User')

@section('content')
    <div class="bg-white p-6 rounded shadow max-w-xl">
        <h2 class="text-xl font-semibold mb-4">Tambah User</h2>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-700 bg-red-100 p-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <label class="block text-sm font-medium mb-1">Nama</label>
            <input name="nama_user" value="{{ old('nama_user') }}" class="w-full border rounded px-3 py-2 mb-3" required>

            <label class="block text-sm font-medium mb-1">Username</label>
            <input name="username" value="{{ old('username') }}" class="w-full border rounded px-3 py-2 mb-3" type="text"
                pattern="[A-Za-z0-9]+" title="Hanya boleh huruf dan angka tanpa spasi" required>

            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-3" required>

            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role" class="w-full border rounded px-3 py-2 mb-3" required>
                @foreach ($roles as $r)
                    <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>

            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2 mb-4">
                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
            </select>

            <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Simpan</button>
        </form>
    </div>
@endsection
