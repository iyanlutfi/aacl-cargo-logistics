<div class="space-y-5" x-data @keydown.escape.window="$wire.closeAllModals()">
    {{-- Header & Search --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-[#0F2744]">Panel Kurir &amp; Driver Lapangan</h1>
                <p class="text-xs text-slate-500">Update progres pickup hingga serah terima (POD) foto &amp; tanda tangan penerima langsung di lokasi.</p>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <button type="button" wire:click="setStatusFilter('pending')"
                        class="px-3 py-1.5 rounded-full text-xs font-bold cursor-pointer border {{ $statusFilter === 'pending' ? 'bg-[#0F2744] text-white border-[#0F2744]' : 'bg-slate-50 text-slate-700 border-slate-200' }}">
                    Belum Selesai (St. 1–4) ({{ $counts['pending'] }})
                </button>
                <button type="button" wire:click="setStatusFilter('completed')"
                        class="px-3 py-1.5 rounded-full text-xs font-bold cursor-pointer border {{ $statusFilter === 'completed' ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-slate-50 text-slate-700 border-slate-200' }}">
                    Selesai (St. 5) ({{ $counts['completed'] }})
                </button>
                <button type="button" wire:click="setStatusFilter('all')"
                        class="px-3 py-1.5 rounded-full text-xs font-bold cursor-pointer border {{ $statusFilter === 'all' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-700 border-slate-200' }}">
                    Semua ({{ $counts['all'] }})
                </button>
            </div>
        </div>

        <div>
            <input type="text" wire:model.live.debounce.250ms="search"
                   placeholder="Cari cepat No. CN, Nama Customer, atau Alamat Tujuan..."
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
        </div>
    </div>

    {{-- Driver Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($cards as $card)
            @php $done = $card->current_stage >= 5; @endphp
            <div class="bg-white rounded-2xl border {{ $done ? 'border-emerald-200' : 'border-slate-200' }} shadow-xs p-5 flex flex-col justify-between gap-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-base font-extrabold text-[#0F2744]">{{ $card->cn_number }}</div>
                            <div class="text-xs text-slate-500">{{ $card->shipment_date }} &bull; Pickup {{ $card->pickup_time ?: '-' }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $done ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                            {{ $done ? 'Selesai / POD' : "Stage {$card->current_stage}/5" }}
                        </span>
                    </div>

                    <div class="text-xs space-y-1.5 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div><span class="text-slate-500">Customer:</span> <strong class="text-slate-900">{{ $card->customer_name }}</strong></div>
                        <div><span class="text-slate-500">Dari:</span> <span class="text-slate-700">{{ $card->shipper_address ?: '-' }}</span></div>
                        <div><span class="text-slate-500">Tujuan:</span> <strong class="text-slate-800">{{ $card->receiver_address ?: '-' }}</strong></div>
                        <div class="pt-1 border-t border-slate-200 flex flex-wrap gap-x-3 text-[11px] font-semibold text-slate-700">
                            <span>{{ $card->colly }} Colly</span>
                            <span>{{ number_format($card->weight_kg, 1) }} Kg</span>
                            <span>{{ number_format($card->volume_m3, 2) }} m&sup3;</span>
                            <span>{{ $card->fleet_name ?: '-' }}</span>
                        </div>
                    </div>

                    {{-- Quick Stage Pills 1..4 --}}
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach([1 => '1. Pickup', 2 => '2. Hub', 3 => '3. Transit', 4 => '4. Delivery'] as $num => $lbl)
                            @php $filled = filled($card->{"status_{$num}_at"}); @endphp
                            <div class="flex rounded-xl overflow-hidden border {{ $filled ? 'border-sky-300 bg-sky-50' : 'border-slate-200 bg-white' }}">
                                <button type="button" wire:click="quickAdvanceStage({{ $card->id }}, {{ $num }})"
                                        class="flex-1 px-2.5 py-1.5 text-left text-[11px] font-bold cursor-pointer {{ $filled ? 'text-sky-900' : 'text-slate-600 hover:bg-slate-50' }}">
                                    {{ $filled ? '✓ ' : '+ ' }}{{ $lbl }}
                                </button>
                                <button type="button" wire:click="openStageModal({{ $card->id }}, {{ $num }})"
                                        class="px-2 py-1.5 text-[10px] font-semibold text-slate-500 hover:bg-slate-100 border-l border-slate-200 cursor-pointer" title="Edit teks status">
                                    Edit
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- POD Action --}}
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div class="text-[11px] text-slate-500 truncate flex items-center gap-1.5 flex-wrap">
                        @if($card->pod_receiver_name)
                            <span>Diterima: <strong class="text-emerald-700">{{ $card->pod_receiver_name }}</strong></span>
                        @else
                            <span>Belum ada POD</span>
                        @endif
                        @if($card->pod_photo_url)
                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold">Foto ✓</span>
                        @endif
                        @if($card->pod_signature_data)
                            <span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 text-[9px] font-bold">TTD ✓</span>
                        @endif
                    </div>
                    <button type="button" wire:click="openPodModal({{ $card->id }})"
                            class="px-3.5 py-2 rounded-xl {{ $done ? 'bg-emerald-100 text-emerald-900 hover:bg-emerald-200' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow' }} text-xs font-bold cursor-pointer shrink-0">
                        {{ $card->hasPod() ? 'Update POD' : 'Selesai & Upload POD' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-sm text-slate-500">
                Tidak ada pengiriman yang cocok dengan pencarian/filter saat ini.
            </div>
        @endforelse
    </div>

    {{-- Stage Edit Modal --}}
    @if($showStageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
                <div class="bg-[#0F2744] px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Edit Status {{ $stageNumber }} — {{ $stageShipmentCn }}</h3>
                    <button type="button" wire:click="$set('showStageModal', false)" class="text-slate-300 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="saveStage" class="p-5 space-y-4">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach([
                            'Barang sudah dijemput kurir',
                            'Masuk Hub Sortir AACL',
                            'Armada dalam perjalanan transit',
                            'Kurir menuju lokasi penerima'
                        ] as $tpl)
                            <button type="button" wire:click="fillStageTemplate('{{ $tpl }}')"
                                    class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-900 text-[11px] font-semibold cursor-pointer">
                                + {{ $tpl }}
                            </button>
                        @endforeach
                    </div>
                    <textarea wire:model="stageValue" rows="3" placeholder="Masukkan catatan status..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                    <div class="flex items-center justify-between gap-2">
                        <button type="button" wire:click="clearStage" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold cursor-pointer">Kosongkan</button>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('showStageModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F2744] text-white text-xs font-bold cursor-pointer">Simpan Status</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Driver POD Modal --}}
    @if($showPodModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-emerald-800 px-5 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-bold">Upload POD Lapangan — {{ $podShipmentCn }}</h3>
                        <p class="text-[11px] text-emerald-100">Foto bukti serah terima &amp; tanda tangan penerima.</p>
                    </div>
                    <button type="button" wire:click="$set('showPodModal', false)" class="text-emerald-200 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="savePod" class="flex-1 min-h-0 flex flex-col overflow-hidden">
                    <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penerima *</label>
                                <input type="text" wire:model="pod_receiver_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                                @error('pod_receiver_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Terima (WIB)</label>
                                <input type="text" wire:model="pod_received_at" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan POD</label>
                            <textarea wire:model="pod_notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Foto Serah Terima</label>
                            <input type="file" wire:model="pod_photo_file" accept="image/*" capture="environment" class="block w-full text-xs text-slate-600">
                            @if($pod_photo_file)
                                <img src="{{ $pod_photo_file->temporaryUrl() }}" class="mt-2 h-24 rounded-xl border object-cover">
                            @elseif($pod_existing_photo_url)
                                <img src="{{ $pod_existing_photo_url }}" class="mt-2 h-24 rounded-xl border object-cover">
                            @endif
                        </div>
                        <div wire:ignore wire:key="driver-pod-sig-{{ $podShipmentId }}" x-data="signaturePad(@js($pod_signature_data))" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700">
                                    Tanda Tangan Penerima
                                    <span x-show="hasDrawn" class="ml-1.5 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px]">TTD Siap Disimpan</span>
                                </label>
                                <button type="button" @click="clear($wire)" class="px-2 py-1 rounded bg-slate-100 text-[11px] font-bold cursor-pointer">Bersihkan</button>
                            </div>
                            <canvas x-ref="canvas" width="520" height="160"
                                    @mousedown="start($event)" @mousemove="move($event)" @mouseup="end($wire)" @mouseleave="end($wire)"
                                    @touchstart.prevent="start($event)" @touchmove.prevent="move($event)" @touchend="end($wire)"
                                    class="w-full h-36 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl touch-none"></canvas>
                        </div>
                    </div>
                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end gap-2 shrink-0">
                        <button type="button" wire:click="$set('showPodModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 rounded-xl bg-emerald-700 text-white text-xs font-bold cursor-pointer disabled:opacity-50">Simpan POD</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
