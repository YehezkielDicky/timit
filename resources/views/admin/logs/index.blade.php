@extends('admin.layout.index')
@section('title', 'Log Aktivitas User')

@section('content')
    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Log Aktivitas User</h2>
        </div>
        <form method="GET" action="" class="mb-4">
            <div class="relative max-w-sm">
                <input type="text" 
                    name="search"
                    placeholder="Cari nama atau no surat..."
                    value="{{ request('search') }}"
                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm
                            focus:outline-none focus:ring focus:ring-purple-300 focus:border-purple-500">
                <svg xmlns="http://www.w3.org/2000/svg" 
                    class="w-5 h-5 text-gray-500 absolute left-3 top-2.5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="border px-3 py-2">#</th>
                        <th class="border px-3 py-2">User</th>
                        <th class="border px-3 py-2">Aktivitas</th>
                        <th class="border px-3 py-2">Modul</th>
                        <th class="border px-3 py-2">Deskripsi</th>
                        <th class="border px-3 py-2">IP</th>
                        <th class="border px-3 py-2">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $index => $log)
                        <tr class="hover:bg-gray-50">
                            <td class="border px-3 py-2">{{ $logs->firstItem() + $index }}</td>
                            <td class="border px-3 py-2 font-semibold text-blue-600">
                                {{ $log->user->nama_user ?? 'Unknown' }}
                            </td>
                            <td class="border px-3 py-2">{{ $log->activity }}</td>
                            <td class="border px-3 py-2">{{ $log->module ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $log->description ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $log->ip_address }}</td>
                            <td class="border px-3 py-2">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-4">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
