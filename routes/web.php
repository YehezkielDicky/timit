<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserLogController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login',  [LoginController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'doLogin'])->name('admin.login.post');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Semua role boleh masuk dashboard & profile
Route::middleware(['role:admin,staff,koordinator'])->group(function () {
    // Route::get('/', fn() => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/profile',  [AdminController::class, 'profile'])->name('admin.profile');
    // aksi profile
    Route::post('/admin/profile/password', [AdminController::class, 'updatePassword'])->name('admin.profile.password');
    Route::post('/admin/profile/photo',    [AdminController::class, 'updatePhoto'])->name('admin.profile.photo');

    // ⬇⬇⬇ Tambahan: Transaksi (CRUD)
    Route::get('/transaksi',               [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/create',        [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::post('/transaksi',              [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::get('/transaksi/{id}/edit',     [TransaksiController::class, 'edit'])->name('transaksi.edit');
    Route::put('/transaksi/{id}',          [TransaksiController::class, 'update'])->name('transaksi.update');
    Route::delete('/transaksi/{id}',       [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    Route::get('/transaksi/laporan', function () {
        return view('admin.transaksi.laporan'); // buat blade placeholder
    })->name('transaksi.report');
    Route::get('/transaksi/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/transaksi/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/transaksi/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

    // ⬇⬇⬇ Tambahan: Aset (hanya store saja)
    Route::post('/aset/store', [AsetController::class, 'store'])->name('aset.store');

    // Barang
    Route::get('/barang',            [BarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/create',     [BarangController::class, 'create'])->name('barang.create');
    Route::post('/barang',           [BarangController::class, 'store'])->name('barang.store');
    Route::get('/barang/{id}/edit',  [BarangController::class, 'edit'])->name('barang.edit');
    Route::put('/barang/{id}',       [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{id}',    [BarangController::class, 'destroy'])->name('barang.destroy');
    Route::get('/barang/{id}/transaksi-json', [BarangController::class, 'transactionsJson'])
        ->name('barang.transactions.json');
    Route::get('/barang/{id}/peminjaman-json', [BarangController::class, 'peminjamanJson']);

    //unit
    Route::resource('unit', UnitController::class)->except(['show', 'create', 'edit']);

    //Peminjaman Barang
    Route::resource('peminjaman', PeminjamanController::class)
        ->except(['show', 'create', 'edit']);
    // tombol kembalikan
    Route::patch('peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan'])
        ->name('peminjaman.kembalikan');
});

// Hanya admin yang bisa kelola user
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/users',               [AdminController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create',        [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/admin/users',              [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::patch('/admin/users/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.users.updateStatus');

    // Reset Password default 123456
    Route::patch(
        '/admin/users/{id}/reset-password',
        [AdminController::class, 'resetUserPassword']
    )->name('admin.users.resetPassword');
});

Route::middleware(['role:admin,koordinator'])->group(function () {
    Route::get('/admin/logs', [UserLogController::class, 'index'])
        ->name('admin.logs.index');
});

//validasi halaman Tiket
Route::middleware(['role:officer'])->group(function () {
    Route::get('/tickets',        [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets',       [TicketController::class, 'store'])->name('tickets.store');
});

Route::middleware(['role:admin,staff,koordinator'])->group(function () {
    Route::get('/tickets/{id}/take', [TicketController::class, 'take'])
        ->name('tickets.take');

    Route::get('/tickets/{id}/done', [TicketController::class, 'done'])
        ->name('tickets.done');
});

Route::middleware(['role:admin,koordinator'])->group(function () {
    Route::get('/tickets-laporan', [TicketController::class, 'laporan'])
        ->name('tickets.laporan');
});