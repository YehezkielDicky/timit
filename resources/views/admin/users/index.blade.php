@extends('admin.layout.index')

@section('title', 'Data User')

@section('content')
    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Data User</h2>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                + Tambah User
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 text-sm text-red-700 bg-red-100 p-3 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" class="mb-4">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama/username/role..."
                class="border rounded px-3 py-2 w-full md:w-72">
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">Nama</th>
                        <th class="p-2 border">Username</th>
                        <th class="p-2 border">Role</th>
                        <th class="p-2 border">Status</th>
                        <th class="p-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="p-2 border">{{ $u->id_user }}</td>
                            <td class="p-2 border">{{ $u->nama_user }}</td>
                            <td class="p-2 border">{{ $u->username }}</td>
                            <td class="p-2 border capitalize">{{ $u->role }}</td>
                            <td class="p-2 border">
                                <span
                                    class="px-2 py-1 rounded text-white
                            @if ($u->status === 'active') bg-green-600 @else bg-gray-500 @endif">
                                    {{ $u->status }}
                                </span>
                            </td>
                            <td class="p-2 border">
                                <form action="{{ route('admin.users.updateStatus', $u->id_user) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status"
                                        value="{{ $u->status === 'active' ? 'inactive' : 'active' }}">
                                    <button class="px-3 py-1 border rounded hover:bg-gray-100">
                                        {{ $u->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                {{-- Reset password (ADMIN ONLY) --}}
                                @if(session('auth_user.role') === 'admin')
                                    <form action="{{ route('admin.users.resetPassword', $u->id_user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="new_password" value="123456">
                                        <button
                                            onclick="return confirm('Reset password user ini ke default 123456 ?')"
                                            class="px-3 py-1 border rounded bg-red-50 text-red-700 hover:bg-red-100 ">
                                            Reset Password
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
