@extends('admin.layout.index')
@section('title', 'Profil Saya')

@section('content')
    <div class="grid md:grid-cols-3 gap-6">
        {{-- Kartu profil --}}
        <div class="bg-white p-6 rounded shadow">
            <div class="flex items-center gap-4">
                @php
                    $photo = $user->photo
                        ? asset('storage/' . $user->photo)
                        : 'https://ui-avatars.com/api/?name=' .
                            urlencode($user->nama_user ?? 'U') .
                            '&background=5b3cc4&color=fff';
                    $badge = match ($user->role) {
                        'admin' => 'bg-green-600',
                        'staff' => 'bg-blue-600',
                        'koordinator' => 'bg-yellow-600',
                        default => 'bg-gray-500',
                    };
                @endphp
                <img src="{{ $photo }}" class="w-16 h-16 rounded-full object-cover border" alt="avatar">
                <div>
                    <div class="text-lg font-semibold">{{ $user->nama_user }}</div>
                    <div class="text-sm text-gray-600">{{ $user->username }}</div>
                    <div class="mt-1">
                        <span
                            class="text-white text-xs px-2 py-0.5 rounded {{ $badge }}">{{ ucfirst($user->role) }}</span>
                        <span
                            class="text-white text-xs px-2 py-0.5 rounded {{ $user->status === 'active' ? 'bg-green-600' : 'bg-gray-500' }}">
                            {{ $user->status }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Ganti foto --}}
            <hr class="my-5">
            <h3 class="font-semibold mb-2">Ganti Foto Profil</h3>

            @if (session('success_photo'))
                <div class="mb-3 text-sm text-green-700 bg-green-100 p-2 rounded">{{ session('success_photo') }}</div>
            @endif
            @error('photo')
                <div class="mb-3 text-sm text-red-700 bg-red-100 p-2 rounded">{{ $message }}</div>
            @enderror

            <form method="POST" action="{{ route('admin.profile.photo') }}" enctype="multipart/form-data"
                class="space-y-3">
                @csrf
                <input type="file" name="photo" accept="image/*" class="w-full border rounded px-3 py-2">
                <div class="text-xs text-gray-500">Format: JPG/PNG/WEBP, maks 2MB.</div>
                <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan Foto</button>
            </form>
        </div>

        {{-- Ubah password --}}
        <div class="md:col-span-2 bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Ubah Password</h3>

            @if (session('success_password'))
                <div class="mb-3 text-sm text-green-700 bg-green-100 p-2 rounded">{{ session('success_password') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.profile.password') }}" class="max-w-lg">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Password Sekarang</label>
                    <input type="password" name="current_password" class="w-full border rounded px-3 py-2" required>
                    @error('current_password')
                        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Password Baru</label>
                        <input type="password" name="new_password" class="w-full border rounded px-3 py-2" minlength="6"
                            required>
                        @error('new_password')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="w-full border rounded px-3 py-2"
                            minlength="6" required>
                        @error('confirm_password')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Update Password</button>
            </form>
        </div>
    </div>
@endsection
