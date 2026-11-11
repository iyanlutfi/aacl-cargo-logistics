<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AACL Cargo & Logistics — Integrated Freight & Tracking System' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full font-sans text-slate-900 antialiased flex flex-col">

    @php
        $user = auth()->user();
        $role = $user?->role;
    @endphp

    {{-- Top Navigation Bar --}}
    <header class="no-print print-hide-on-cn bg-[#0F2744] text-white shadow-lg sticky top-0 z-40 border-b border-white/10">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                {{-- Brand --}}
                <a href="{{ route('public.tracking') }}" wire:navigate class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-md shadow-sky-900/40 shrink-0 font-extrabold text-sm tracking-tight text-white">
                        AACL
                    </div>
                    <div>
                        <div class="font-extrabold text-base tracking-tight leading-tight group-hover:text-sky-300 transition">
                            AACL Cargo &amp; Logistics
                        </div>
                        <div class="text-[11px] text-sky-200/80 font-medium">
                            Integrated Freight, Fleet &amp; Trace System
                        </div>
                    </div>
                </a>

                {{-- Navigation Tabs --}}
                <nav class="flex flex-wrap items-center gap-1.5 bg-white/5 p-1 rounded-xl border border-white/10">
                    <a href="{{ route('public.tracking') }}" wire:navigate
                       class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition {{ request()->routeIs('public.tracking') ? 'bg-sky-500 text-white shadow' : 'text-slate-200 hover:bg-white/10' }}">
                        Cek Resi Publik
                    </a>

                    @if(in_array($role, ['owner', 'admin'], true))
                        <a href="{{ route('operasional') }}" wire:navigate
                           class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition {{ request()->routeIs('operasional') ? 'bg-sky-500 text-white shadow' : 'text-slate-200 hover:bg-white/10' }}">
                            Operasional Spreadsheet
                        </a>
                    @endif

                    @if(in_array($role, ['owner', 'driver'], true))
                        <a href="{{ route('kurir') }}" wire:navigate
                           class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition {{ request()->routeIs('kurir') ? 'bg-sky-500 text-white shadow' : 'text-slate-200 hover:bg-white/10' }}">
                            Panel Kurir
                        </a>
                    @endif

                    @if(in_array($role, ['owner', 'admin'], true))
                        <a href="{{ route('master') }}" wire:navigate
                           class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition {{ request()->routeIs('master') ? 'bg-sky-500 text-white shadow' : 'text-slate-200 hover:bg-white/10' }}">
                            Master Customer &amp; Armada
                        </a>
                    @endif

                    @if($role === 'owner')
                        <a href="{{ route('owner') }}" wire:navigate
                           class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition {{ request()->routeIs('owner') ? 'bg-amber-500 text-slate-950 shadow' : 'text-amber-200 hover:bg-white/10' }}">
                            Owner &amp; Karyawan
                        </a>
                    @endif
                </nav>

                {{-- User / Auth Controls --}}
                <div class="flex items-center gap-2.5">
                    @if($user)
                        @php
                            $roleBadge = match ($role) {
                                'owner' => 'bg-amber-400/20 text-amber-300 border-amber-400/40',
                                'admin' => 'bg-sky-400/20 text-sky-200 border-sky-400/40',
                                default => 'bg-emerald-400/20 text-emerald-200 border-emerald-400/40',
                            };
                            $roleLabel = match ($role) {
                                'owner' => 'OWNER',
                                'admin' => 'ADMIN OPS',
                                default => 'KURIR',
                            };
                        @endphp
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/10 border border-white/10">
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded border {{ $roleBadge }}">
                                {{ $roleLabel }}
                            </span>
                            <span class="text-xs font-semibold text-white truncate max-w-[160px]">
                                {{ $user->full_name }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-200 hover:text-white border border-rose-400/30 text-xs font-semibold transition cursor-pointer">
                                Keluar
                            </button>
                        </form>
                    @else
                        <button type="button"
                            x-data
                            @click="$dispatch('open-login-modal')"
                            class="px-3.5 py-2 rounded-lg bg-sky-500 hover:bg-sky-400 text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                            </svg>
                            Login Internal
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- Main Page Content --}}
    <main class="flex-1 w-full max-w-[1600px] mx-auto px-4 sm:px-6 py-5">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="no-print print-hide-on-cn bg-white border-t border-slate-200 py-3 px-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} PT. AACL Cargo &amp; Logistics &bull; Sistem Operasional &amp; Tracking Terpadu (WIB)
    </footer>

    {{-- Clean Login Modal --}}
    <livewire:login-modal />

    {{-- Toast Notification Stack --}}
    <div x-data="toastManager"
         @toast.window="add($event.detail)"
         class="no-print fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
        @if(session('error'))
            <span wire:key="flash-err-{{ md5(session('error').microtime(true)) }}"
                  x-init="$nextTick(() => add({ message: @js(session('error')), type: 'error' }))"></span>
        @elseif(session('success'))
            <span wire:key="flash-ok-{{ md5(session('success').microtime(true)) }}"
                  x-init="$nextTick(() => add({ message: @js(session('success')), type: 'success' }))"></span>
        @endif
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition
                 class="pointer-events-auto flex items-start gap-2.5 px-4 py-3 rounded-xl shadow-xl border text-sm font-medium"
                 :class="{
                    'bg-emerald-900 text-white border-emerald-700': toast.type === 'success',
                    'bg-rose-900 text-white border-rose-700': toast.type === 'error',
                    'bg-[#0F2744] text-white border-sky-500/40': toast.type === 'info'
                 }">
                <span class="flex-1" x-text="toast.message"></span>
                <button type="button" @click="remove(toast.id)" class="text-white/70 hover:text-white text-xs font-bold cursor-pointer">&times;</button>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
