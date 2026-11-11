<div>
    @if($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
             x-data
             @keydown.escape.window="$wire.closeModal()">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden"
                 @click.outside="$wire.closeModal()">
                <div class="bg-[#0F2744] px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold">Login Portal Internal AACL</h3>
                        <p class="text-xs text-sky-200/80">Masuk sesuai akun Owner, Admin Operasional, atau Kurir</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="text-slate-300 hover:text-white text-xl leading-none cursor-pointer">&times;</button>
                </div>

                <form wire:submit="authenticate" class="p-6 space-y-4">
                    @if($errorMessage)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username</label>
                        <input type="text" wire:model="username" autofocus autocomplete="username"
                               placeholder="Masukkan username"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                        @error('username')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                        <input type="password" wire:model="password" autocomplete="current-password"
                               placeholder="Masukkan password"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        Ingat sesi saya di perangkat ini
                    </label>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-semibold cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-sm font-bold shadow cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="authenticate">Masuk Sekarang</span>
                            <span wire:loading wire:target="authenticate">Memverifikasi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
