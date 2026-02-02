<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    {{-- ANTI ZOOM MOBILE --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>GA Portal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 overflow-x-hidden">

<!-- ===== MOBILE OVERLAY ===== -->
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"
     onclick="toggleSidebar()">
</div>

<!-- ===== SIDEBAR ===== -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-screen w-60 bg-[#F7F8F9] border-r z-40
              transform -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out">

    <!-- Logo -->
    <div class="px-6 py-5 border-b">
        <h1 class="text-xl font-semibold text-gray-800">GA Portal</h1>
        <p class="text-sm text-gray-500 mt-1">General Affairs</p>
    </div>

    <!-- Menu -->
    <nav class="mt-4 flex flex-col text-[15px] font-medium">

        <a href="/dashboard" class="menu-item {{ request()->is('dashboard') ? 'active-menu' : '' }}">
            Beranda
        </a>

        <a href="/karyawan" class="menu-item {{ request()->is('karyawan*') ? 'active-menu' : '' }}">
            Karyawan & Grup
        </a>

        <a href="/rules" class="menu-item {{ request()->is('rules*') ? 'active-menu' : '' }}">
            Limit & Aturan
        </a>

        <a href="/riwayat" class="menu-item {{ request()->is('riwayat*') ? 'active-menu' : '' }}">
            Riwayat
        </a>

        <a href="/pembayaran" class="menu-item {{ request()->is('pembayaran*') ? 'active-menu' : '' }}">
            Pembayaran
        </a>

        <a href="/voucher" class="menu-item {{ request()->is('voucher*') ? 'active-menu' : '' }}">
            Kode Voucher
        </a>

        <a href="/setelan" class="menu-item {{ request()->is('setelan*') ? 'active-menu' : '' }}">
            Setelan
        </a>

        <a href="/bantuan" class="menu-item {{ request()->is('bantuan*') ? 'active-menu' : '' }}">
            Pusat Bantuan
        </a>

    </nav>
</aside>

<!-- ===== MAIN WRAPPER ===== -->
<div class="lg:ml-60 min-h-screen flex flex-col">

    <!-- ===== TOPBAR ===== -->
    <header class="sticky top-0 z-20 bg-white border-b">
        <div class="flex items-center justify-between px-4 py-3">

            <!-- Hamburger (mobile) -->
            <button onclick="toggleSidebar()"
                    class="lg:hidden p-2 rounded-md hover:bg-gray-100">
                ☰
            </button>

            <div class="font-semibold text-gray-700 truncate">
                @yield('page-title', 'Dashboard')
            </div>

            <div class="text-sm text-gray-500 truncate max-w-[120px]">
                {{ auth()->user()->name ?? 'User' }}
            </div>
        </div>
    </header>

    <!-- ===== PAGE CONTENT ===== -->
    <main class="flex-1 p-4 sm:p-6 overflow-x-hidden">
        @yield('content')
    </main>

</div>

<!-- ===== SCRIPT ===== -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar')
    const overlay = document.getElementById('sidebarOverlay')

    sidebar.classList.toggle('-translate-x-full')
    overlay.classList.toggle('hidden')
}
</script>

</body>
</html>
