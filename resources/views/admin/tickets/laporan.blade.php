@extends('admin.layout.index')
@section('title', 'Laporan Tiket')

@section('content')

    <div class="bg-white p-4 rounded shadow">

        <h2 class="text-lg font-semibold mb-4">Laporan Tiket Selesai</h2>

        {{-- FILTER BULAN --}}
        <form method="GET" class="flex gap-3 mb-4">
            <select name="bulan" class="border px-2 py-1 rounded">
                <option value="">Semua Bulan</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" @selected(request('bulan') == $i)>
                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                    </option>
                @endfor
            </select>

            <input type="text" name="staff" placeholder="Nama Staff" value="{{ request('staff') }}"
                class="border px-2 py-1 rounded">

            <button class="px-4 py-1 bg-blue-600 text-white rounded">
                Filter
            </button>

            {{-- Tombol Print --}}
            <button type="button"
                onclick="window.print()"
                class="ml-auto px-4 py-1 bg-green-600 text-white rounded no-print">
                Print
            </button>

        </form>

        {{-- TABLE --}}
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-2">No</th>
                    <th class="border px-2 py-2">Ticket</th>
                    <th class="border px-2 py-2">Judul</th>
                    <th class="border px-2 py-2">Unit</th>
                    <th class="border px-2 py-2">Staff</th>
                    <th class="border px-2 py-2">Dibuat</th>
                    <th class="border px-2 py-2">Selesai</th>
                    <th class="border px-2 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $i => $t)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $t->ticket_number }}</td>
                        <td class="border px-2 py-1">{{ $t->judul }}</td>
                        <td class="border px-2 py-1">
                            {{ $t->unit ? $t->unit->unit_kerja . ' (' . $t->unit->lokasi . ')' : '-' }}
                        </td>
                        <td class="border px-2 py-1">
                            {{ $t->staff->nama_user ?? '-' }}
                        </td>
                        <td class="border px-2 py-1">
                            {{ $t->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="border px-2 py-1">
                            {{ $t->updated_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <span class="px-2 py-1 rounded text-xs bg-green-200">
                                {{ strtoupper($t->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">
                            Tidak ada data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- CSS untuk print --}}
    <style>
        @media print {
            nav, header, footer, .no-print, .bg-blue-600 {
                display: none !important;
            }
            body {
                background: white !important;
            }
        }
    </style>

@endsection
