<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('assets/img/logo.png') }}" type="image/png">
</head>
@php
    $me = session('auth_user');
    $role = $me['role'] ?? null;

    $roleBadge = match ($role) {
        'admin' => 'bg-green-600',
        'staff' => 'bg-blue-600',
        'koordinator' => 'bg-yellow-600',
        'officer' => 'bg-gray-500',
        default => 'bg-gray-500',
    };

    $isAdmin = $role === 'admin';
    $isOfficer = $role === 'officer';
    $canSeeTicket = in_array($role, ['admin', 'staff', 'koordinator', 'officer']);
    $canSeeTicketReport = in_array($role, ['admin', 'koordinator']);
@endphp
<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    {{-- Sidebar kiri --}}
    <aside id="sidebar"
           class="w-64 shrink-0 bg-[#5b3cc4] text-white hidden md:block transition-all duration-300 ease-in-out">
        <div class="px-4 py-4 flex items-center gap-2 border-b border-white/10">
            <span class="text-lg font-bold">Teknisi UKWMS</span>
        </div>

        <nav class="px-3 py-4 text-sm">
            <div class="uppercase text-white/70 px-2 mb-2"></div>

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded
               {{ request()->routeIs('admin.dashboard') ? 'bg-white/15' : 'hover:bg-white/10' }}">
                <span>Dashboard</span>
            </a>

            <div class="mt-4 uppercase text-white/70 px-2 mb-2">Manajemen</div>

            @if ($isAdmin)
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded
                   {{ request()->routeIs('admin.users.*') ? 'bg-white/15' : 'hover:bg-white/10' }}">
                    <span>User</span>
                </a>
            @endif

            @if (!$isOfficer)
                <a href="{{ route('transaksi.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded
                {{ request()->routeIs('transaksi.*') ? 'bg-white/15' : 'hover:bg-white/10' }}">
                    <span>Transaksi</span>
                </a>

                <a href="{{ route('barang.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded
                {{ request()->routeIs('barang.*') ? 'bg-white/15' : 'hover:bg-white/10' }}">
                    <span>Master Barang</span>
                </a>

                <a href="{{ route('unit.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/10">
                    <span>Master Unit</span>
                </a>

                <a href="{{ route('peminjaman.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/10">
                    <span>Peminjaman Barang</span>
                </a>
            @endif

            @if (in_array(session('auth_user.role'), ['admin', 'koordinator']))
                    <a href="{{ route('admin.logs.index') }}"
                       class="block py-2 px-3 hover:bg-white/10 rounded">
                        Log User
                    </a>
            @endif

             {{-- MENU TIKET / COMPLAIN --}}
                @if ($canSeeTicket)
                    <a href="{{ route('tickets.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded
        {{ request()->routeIs('tickets.*') && !request()->routeIs('tickets.laporan')
            ? 'bg-white/15'
            : 'hover:bg-white/10' }}">
                        <span>Tiket</span>
                    </a>
                @endif

                {{-- MENU LAPORAN TIKET --}}
                @if ($canSeeTicketReport)
                    <a href="{{ route('tickets.laporan') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded
        {{ request()->routeIs('tickets.laporan') ? 'bg-white/15' : 'hover:bg-white/10' }}">
                        <span>Laporan Tiket</span>
                    </a>
                @endif
        </nav>
    </aside>

    {{-- Konten kanan --}}
    <div class="flex-1 flex flex-col">

        {{-- Topbar --}}
        <header class="h-14 bg-white border-b flex items-center justify-between px-4">
            <div class="flex items-center gap-2">
                <!-- tombol "=" di topbar: satu tombol untuk mobile & desktop -->
                <button id="btnMobile"
                        class="inline-flex items-center justify-center p-2 rounded hover:bg-gray-100 text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="font-semibold">@yield('title', 'Admin Panel')</h1>
            </div>

            {{-- Profil kanan atas --}}
            <div class="relative">
                <button id="profileBtn" class="flex items-center gap-3 rounded px-2 py-1 hover:bg-gray-100">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-medium">{{ $me['name'] ?? 'Guest' }}</div>
                        <div class="text-xs">
                            <span class="text-white px-2 py-0.5 rounded {{ $roleBadge }}">
                                {{ ucfirst($me['role'] ?? '-') }}
                            </span>
                        </div>
                    </div>
                    @php
                        $me = session('auth_user');
                        $user = \App\Models\User::find($me['id']);
                        $avatar = $user && $user->photo
                            ? asset('storage/' . $user->photo)
                            : 'https://ui-avatars.com/api/?name=' . urlencode($user->nama_user ?? 'U') . '&background=5b3cc4&color=fff';
                    @endphp
                    <img src="{{ $avatar }}" class="w-9 h-9 rounded-full border object-cover" alt="avatar">
                </button>

                {{-- Dropdown profil --}}
                <div id="profileMenu"
                     class="absolute right-0 mt-2 w-48 bg-white border rounded shadow hidden z-10">
                    <a href="{{ route('admin.profile') }}"
                       class="block px-3 py-2 text-sm hover:bg-gray-50">Profil</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 text-red-600">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Isi halaman --}}
        <main class="p-4">
            @yield('content')
        </main>

        <footer class="text-center text-xs text-gray-500 py-3">
            &copy; {{ date('Y') }} Dibuat oleh Dicky Tampan
        </footer>
    </div>
</div>

{{-- Sidebar drawer untuk mobile --}}
<div id="drawer" class="fixed inset-0 bg-black/30 z-20 hidden md:hidden">
    <div class="absolute left-0 top-0 h-full w-64 bg-[#5b3cc4] text-white p-0">
        <div class="px-4 py-4 flex items-center gap-2 border-b border-white/10">
            <span class="text-lg font-bold">Teknisi UKWMS</span>
        </div>
        <nav class="px-3 py-4 text-sm">
            <a href="{{ route('admin.dashboard') }}"
               class="block px-3 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-white/15' : 'hover:bg-white/10' }}">Dashboard</a>
            @if ($isAdmin)
                <a href="{{ route('admin.users.index') }}"
                   class="block px-3 py-2 rounded {{ request()->routeIs('admin.users.*') ? 'bg-white/15' : 'hover:bg-white/10' }}">User</a>
            @endif
            <a href="{{ route('transaksi.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">Transaksi</a>
            <a href="{{ route('barang.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">Master Barang</a>
            <a href="{{ route('unit.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">Master Unit</a>
            <a href="{{ route('peminjaman.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">Peminjaman</a>
            @if (in_array(session('auth_user.role'), ['admin', 'koordinator']))
                <a href="{{ route('admin.logs.index') }}" class="block px-3 py-2 rounded hover:bg-white/10">Log User</a>
            @endif
        </nav>
    </div>
</div>

<script>
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    const btnMobile   = document.getElementById('btnMobile');     // tombol "=" di topbar
    const drawer      = document.getElementById('drawer');
    const sidebar     = document.getElementById('sidebar');
    const toggleSide  = document.getElementById('toggleSidebar'); // tombol "=" di header sidebar

    // Toggle dropdown profil
    profileBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('hidden');
    });
    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.add('hidden');
        }
    });

    // Fungsi: toggle sidebar desktop (collapse/expand)
    function toggleDesktopSidebar() {
        // geser sidebar keluar layar
        sidebar.classList.toggle('-ml-64');
    }

    // Klik tombol di topbar:
    // - Mobile: buka/utup drawer
    // - Desktop: collapse/expand sidebar
    btnMobile?.addEventListener('click', () => {
        if (window.innerWidth < 768) {
            drawer.classList.toggle('hidden');
        } else {
            toggleDesktopSidebar();
        }
    });

    // Klik tombol di header sidebar (ikon "=")
    toggleSide?.addEventListener('click', toggleDesktopSidebar);

    // Tutup drawer jika klik area gelap
    drawer?.addEventListener('click', (e) => {
        if (e.target === drawer) drawer.classList.add('hidden');
    });

    // Responsif: jika resize dari mobile ke desktop saat drawer terbuka, tutup drawer
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            drawer.classList.add('hidden');
        }
    });
</script>
</body>
</html>
