<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    // daftar tiket (officer)
    public function index(Request $request)
    {
        $me   = session('auth_user');
        $role = $me['role'] ?? null;
        $userId = $me['id'] ?? null;

        $query = Ticket::with('unit')->orderByDesc('id_ticket');

        // 🔹 OFFICER: hanya lihat tiket yang dia input
        if ($role === 'officer') {
            $query->where('id_staff', $userId);
        }

        // 🔹 STAFF / KOORDINATOR / ADMIN: lihat PENDING ticket saja
        if (in_array($role, ['staff', 'koordinator', 'admin'])) {
            $query->whereIn('status', ['new', 'process']);
        }

        $tickets = $query->get();

        return view('admin.tickets.index', compact('tickets', 'role'));
    }


    // form input tiket
    public function create()
    {
        $units = UnitKerja::orderBy('unit_kerja')->get();
        return view('admin.tickets.create', compact('units'));
    }

    // simpan tiket
    public function store(Request $request)
    {
        $request->validate([
            'judul'          => 'required|max:150',
            'deskripsi'      => 'required',
            'nama_pelapor'   => 'required|max:100',
            'kontak_pelapor' => 'nullable|max:100',
            'id_unit'        => 'required|exists:unit_kerja,id_unit',
        ]);

        $me = session('auth_user'); // ⬅️ ambil user login

        $ticket = Ticket::create([
            'ticket_number'  => 'TCK-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
            'judul'          => $request->judul,
            'deskripsi'      => $request->deskripsi,
            'nama_pelapor'   => $request->nama_pelapor,
            'kontak_pelapor' => $request->kontak_pelapor,
            'id_unit'        => $request->id_unit,
            'id_staff'       => $me['id'], // ⬅️ INI KUNCINYA
            'status'         => 'new',
        ]);

        TicketLog::create([
            'id_ticket'   => $ticket->id_ticket,
            'status_from' => null,
            'status_to'   => 'new',
            'catatan'     => 'Tiket dibuat oleh officer',
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket berhasil dibuat');
    }


    public function take($id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status'   => 'process',
            'id_staff' => session('auth_user.id'),
        ]);

        TicketLog::create([
            'id_ticket'   => $ticket->id_ticket,
            'status_from' => 'new',
            'status_to'  => 'process',
            'catatan'    => 'Diambil oleh staff',
        ]);

        return back()->with('success', 'Tiket diambil');
    }

    public function done($id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status' => 'done'
        ]);

        TicketLog::create([
            'id_ticket'   => $ticket->id_ticket,
            'status_from' => 'process',
            'status_to'  => 'done',
            'catatan'    => 'Tiket selesai',
        ]);

        return back()->with('success', 'Tiket selesai');
    }
    public function laporan(Request $request)
    {
        $me = session('auth_user');

        // keamanan tambahan (jangan percaya layout)
        if (!in_array($me['role'], ['admin', 'koordinator'])) {
            abort(403);
        }

        $query = Ticket::with(['unit', 'staff']);

        // filter bulan
        if ($request->bulan) {
            $query->whereMonth('created_at', $request->bulan);
        }

        // search nama staff
        if ($request->staff) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->where('nama_user', 'like', '%' . $request->staff . '%');
            });
        }

        $tickets = $query->orderByDesc('id_ticket')->get();

        // Jika parameter print ada → arahkan ke view khusus print
        if ($request->has('print')) {
            return view('admin.tickets.print', compact('tickets'));
        }

        return view('admin.tickets.laporan', compact('tickets'));
    }
}