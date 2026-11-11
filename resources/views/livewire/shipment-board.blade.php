<div class="space-y-4" x-data @keydown.escape.window="$wire.closeAllModals()">
    {{-- Print-Only Kop Banner (Landscape Table Mode) --}}
    <div class="hidden print-header-only border-b-2 border-[#0F2744] pb-2 mb-3">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-base font-extrabold text-[#0F2744]">{{ strtoupper($company?->company_name ?: 'PT. AACL CARGO & LOGISTICS') }}</div>
                <div class="text-[10px] text-slate-600">Laporan Operasional Pengiriman &amp; Tracking Cargo (Mode Cetak A4 Landscape)</div>
            </div>
            <div class="text-right text-[10px] text-slate-600">
                <div>Dicetak: {{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                <div>Total: {{ $stats['total_cn'] }} CN &bull; {{ number_format($stats['total_colly']) }} Colly &bull; Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    {{-- Top Toolbar --}}
    <div class="no-print print-hide-on-cn bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-extrabold text-[#0F2744]">Spreadsheet Operasional Pengiriman</h1>
            <p class="text-xs text-slate-500">Klik sel Status (1–5), POD, atau Harga &amp; Bayar langsung di tabel untuk pembaruan cepat.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="openCreateModal" wire:loading.attr="disabled"
                    class="px-3.5 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs sm:text-sm font-bold shadow cursor-pointer flex items-center gap-1.5">
                <span>+ Input Pengiriman (CN)</span>
            </button>

            <a href="{{ route('export.xlsx', $exportParams) }}"
               class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-xs transition">
                Export Excel (.xlsx)
            </a>

            <a href="{{ route('export.csv', $exportParams) }}"
               class="px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold shadow-xs transition">
                Export CSV
            </a>

            <button type="button" wire:click="openImportModal"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-xs font-bold cursor-pointer transition">
                Import CSV
            </button>

            <button type="button" @click="window.aaclPrintTable()"
                    class="px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold shadow-xs cursor-pointer transition">
                Cetak Tabel (A4 Landscape)
            </button>
        </div>
    </div>

    {{-- 5 KPI Cards --}}
    <div class="no-print print-hide-on-cn grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Pengiriman</div>
            <div class="text-2xl font-extrabold text-[#0F2744] mt-1">{{ number_format($stats['total_cn']) }} <span class="text-xs font-semibold text-slate-500">CN</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Sesuai filter aktif</div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Muatan</div>
            <div class="text-lg font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_colly']) }} <span class="text-xs font-semibold text-slate-500">Colly</span></div>
            <div class="text-[11px] text-slate-600 mt-1">
                {{ number_format($stats['total_weight'], 2) }} Kg &bull; {{ number_format($stats['total_volume'], 2) }} m&sup3; &bull; {{ number_format($stats['total_area'], 2) }} m&sup2;
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Dalam Proses (St. 1–4)</div>
            <div class="text-2xl font-extrabold text-sky-600 mt-1">{{ number_format($stats['in_progress']) }} <span class="text-xs font-semibold text-slate-500">CN</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Aktif berjalan di jalur / kurir</div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Selesai &amp; POD (St. 5)</div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['delivered']) }} <span class="text-xs font-semibold text-slate-500">CN</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Terkirim lengkap</div>
        </div>

        <div class="bg-gradient-to-br from-[#0F2744] to-[#17375e] rounded-2xl p-4 text-white shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wider text-sky-200">Total Harga Kirim</div>
            <div class="text-xl font-extrabold mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-sky-100/90 mt-1 flex flex-wrap gap-x-2">
                <span class="text-emerald-300">Lunas: Rp {{ number_format($stats['paid_revenue'], 0, ',', '.') }}</span>
                <span class="text-amber-300">Piutang: Rp {{ number_format($stats['unpaid_revenue'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Pipeline Stage Pills & Filter Controls --}}
    <div class="no-print print-hide-on-cn bg-white rounded-2xl border border-slate-200 p-4 shadow-xs space-y-3">
        {{-- Stage Pipeline Pills --}}
        <div class="flex flex-wrap items-center gap-1.5">
            @php
                $pills = [
                    'all' => ['label' => 'Semua Stage', 'count' => $pipelineStats['total_cn']],
                    '1' => ['label' => 'St. 1 Pickup', 'count' => $pipelineStats['stage_counts'][1] ?? 0],
                    '2' => ['label' => 'St. 2 Hub/Sortir', 'count' => $pipelineStats['stage_counts'][2] ?? 0],
                    '3' => ['label' => 'St. 3 Transit', 'count' => $pipelineStats['stage_counts'][3] ?? 0],
                    '4' => ['label' => 'St. 4 Delivery', 'count' => $pipelineStats['stage_counts'][4] ?? 0],
                    '5' => ['label' => 'St. 5 Selesai/POD', 'count' => $pipelineStats['stage_counts'][5] ?? 0],
                ];
                if (($pipelineStats['stage_counts'][0] ?? 0) > 0) {
                    $pills['0'] = ['label' => 'Terjadwal (St. 0)', 'count' => $pipelineStats['stage_counts'][0]];
                }
            @endphp
            @foreach($pills as $key => $pill)
                <button type="button" wire:click="setStageFilter('{{ $key }}')"
                        class="px-3 py-1.5 rounded-full text-xs font-bold transition cursor-pointer flex items-center gap-1.5 border {{ $stage === (string) $key ? 'bg-[#0F2744] text-white border-[#0F2744]' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <span>{{ $pill['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $stage === (string) $key ? 'bg-sky-500 text-white' : 'bg-slate-200 text-slate-700' }}">
                        {{ $pill['count'] }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Search, Date & Payment Filters --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2.5 items-end">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari CN / Customer / Alamat / Armada</label>
                <input type="text" wire:model.live.debounce.250ms="search"
                       placeholder="Ketik kata kunci pencarian..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Pembayaran</label>
                <select wire:model.live="paymentStatus"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 outline-none bg-white">
                    <option value="all">Semua Pembayaran</option>
                    <option value="unpaid_all">Semua Belum Lunas (Piutang)</option>
                    @foreach($paymentOptions as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="button" wire:click="resetFilters"
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs sm:text-sm font-bold cursor-pointer">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- 19-Column Spreadsheet Table --}}
    <div class="print-hide-on-cn spreadsheet-wrapper bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="no-print px-4 py-2 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
            <span>Menampilkan <strong class="text-slate-800">{{ $shipments->count() }}</strong> pengiriman</span>
            <span>Tip: Kolom <strong>No. CN</strong> terkunci di kiri saat tabel digeser horizontal. Klik sel Status, POD, atau Harga untuk update instan.</span>
        </div>

        <div class="spreadsheet-scroll overflow-x-auto max-h-[68vh]">
            <table id="shipmentSpreadsheet" class="min-w-full border-collapse text-xs">
                <thead class="bg-[#0F2744] text-white sticky top-0 z-20">
                    <tr>
                        <th rowspan="2" class="md:sticky md:left-0 z-30 bg-[#0F2744] border border-slate-700 px-2.5 py-2 text-center font-bold whitespace-nowrap">No. CN</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-center font-bold whitespace-nowrap">Tanggal</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-left font-bold min-w-[150px]">Nama Cust</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-left font-bold min-w-[160px]">Alamat Pengirim</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-left font-bold min-w-[170px]">Alamat Penerima</th>
                        <th colspan="4" class="border border-slate-700 px-2 py-1 text-center font-bold bg-[#1E40AF]">Jumlah Muatan</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-left font-bold min-w-[140px]">Armada</th>
                        <th rowspan="2" class="border border-slate-700 px-2 py-2 text-center font-bold whitespace-nowrap">Pickup</th>
                        <th colspan="5" class="border border-slate-700 px-2 py-1 text-center font-bold bg-[#1E40AF]">Status Tracking (Klik Sel untuk Update)</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-left font-bold min-w-[165px]">Bukti Diterima (POD)</th>
                        <th rowspan="2" class="border border-slate-700 px-2.5 py-2 text-right font-bold min-w-[145px]">Harga &amp; Status Bayar</th>
                        <th rowspan="2" class="print-hide-col border border-slate-700 px-2.5 py-2 text-center font-bold whitespace-nowrap">Aksi</th>
                    </tr>
                    <tr class="bg-[#DBEAFE] text-[#0F2744]">
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold">Colly</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold">Kg</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold sub-dim">Vol (m&sup3;)</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold">m&sup2;</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold min-w-[130px]">1. Pickup</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold min-w-[130px]">2. Hub</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold min-w-[130px]">3. Transit</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold min-w-[130px]">4. Delivery</th>
                        <th class="border border-slate-300 px-2 py-1 text-center font-bold min-w-[130px]">5. Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($shipments as $s)
                        @php
                            $payClass = match ($s->payment_status) {
                                'Lunas' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'DP / Sebagian' => 'bg-sky-100 text-sky-800 border-sky-200',
                                'Tagihan TOP 7 Hari', 'Tagihan TOP 14 Hari', 'Tagihan TOP 30 Hari' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-rose-100 text-rose-800 border-rose-200',
                            };
                        @endphp
                        <tr class="group hover:bg-sky-50/60 transition align-top odd:bg-white even:bg-slate-50/70">
                            <td class="md:sticky md:left-0 z-10 bg-white group-even:bg-slate-50 group-hover:bg-sky-50 border border-slate-200 px-2.5 py-2 font-extrabold text-[#0F2744] whitespace-nowrap text-center shadow-[1px_0_0_#e2e8f0]">
                                {{ $s->cn_number }}
                                <div class="text-[10px] font-semibold mt-0.5 {{ $s->current_stage >= 5 ? 'text-emerald-600' : 'text-sky-600' }}">
                                    Stage {{ $s->current_stage }}/5
                                </div>
                            </td>
                            <td class="border border-slate-200 px-2 py-2 whitespace-nowrap text-center text-slate-700">{{ $s->shipment_date }}</td>
                            <td class="border border-slate-200 px-2.5 py-2 font-semibold text-slate-900">{{ $s->customer_name }}</td>
                            <td class="border border-slate-200 px-2.5 py-2 text-slate-600 text-[11px]">{{ $s->shipper_address ?: '-' }}</td>
                            <td class="border border-slate-200 px-2.5 py-2 text-slate-700 text-[11px]">{{ $s->receiver_address ?: '-' }}</td>

                            <td class="border border-slate-200 px-2 py-2 text-right font-semibold tabular-nums">{{ number_format($s->colly) }}</td>
                            <td class="border border-slate-200 px-2 py-2 text-right tabular-nums">{{ number_format($s->weight_kg, 2) }}</td>
                            <td class="border border-slate-200 px-2 py-2 text-right tabular-nums">{{ number_format($s->volume_m3, 3) }}</td>
                            <td class="border border-slate-200 px-2 py-2 text-right tabular-nums">{{ number_format($s->area_m2, 2) }}</td>

                            <td class="border border-slate-200 px-2.5 py-2 text-slate-700 text-[11px]">{{ $s->fleet_name ?: '-' }}</td>
                            <td class="border border-slate-200 px-2 py-2 text-center whitespace-nowrap text-slate-600">{{ $s->pickup_time ?: '-' }}</td>

                            @for($st = 1; $st <= 5; $st++)
                                @php $stVal = $s->{"status_{$st}_at"}; @endphp
                                <td wire:click="openStageModal({{ $s->id }}, {{ $st }})"
                                    title="Klik untuk update Status {{ $st }}"
                                    class="border border-slate-200 px-2 py-2 text-[11px] cursor-pointer transition {{ filled($stVal) ? ($st === 5 ? 'bg-emerald-50/70 text-emerald-900 hover:bg-emerald-100' : 'bg-sky-50/60 text-slate-800 hover:bg-sky-100') : 'text-slate-400 hover:bg-slate-100 italic' }}">
                                    {{ $stVal ?: '+ Isi St.'.$st }}
                                </td>
                            @endfor

                            {{-- POD Cell --}}
                            <td wire:click="openPodModal({{ $s->id }})"
                                title="Klik untuk kelola POD & Tanda Tangan Digital"
                                class="border border-slate-200 px-2.5 py-2 text-[11px] cursor-pointer hover:bg-emerald-50 transition">
                                @if($s->hasPod())
                                    <div class="font-bold text-emerald-800">{{ $s->pod_receiver_name ?: 'POD Tersedia' }}</div>
                                    @if($s->pod_received_at)
                                        <div class="text-[10px] text-slate-500">{{ $s->pod_received_at }}</div>
                                    @endif
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($s->pod_photo_url)
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold">Foto</span>
                                        @endif
                                        @if($s->pod_signature_data)
                                            <span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 text-[9px] font-bold">TTD</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">+ Upload POD</span>
                                @endif
                            </td>

                            {{-- Pricing & Payment Cell --}}
                            <td wire:click="openPricingModal({{ $s->id }})"
                                title="Klik untuk edit Harga & Status Bayar"
                                class="border border-slate-200 px-2.5 py-2 text-right cursor-pointer hover:bg-amber-50 transition">
                                <div class="font-extrabold text-slate-900 tabular-nums">
                                    Rp {{ number_format($s->service_cost, 0, ',', '.') }}
                                </div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $payClass }}">
                                    {{ $s->payment_status ?: 'Belum Lunas' }}
                                </span>
                            </td>

                            {{-- Action Buttons --}}
                            <td class="print-hide-col border border-slate-200 px-2 py-2 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" wire:click="openPrintModal({{ $s->id }})"
                                            class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold cursor-pointer" title="Cetak Surat Jalan / Resi CN">
                                        Resi
                                    </button>
                                    <button type="button" wire:click="openEditModal({{ $s->id }})"
                                            class="px-2 py-1 rounded-lg bg-sky-100 hover:bg-sky-200 text-sky-900 text-[11px] font-bold cursor-pointer" title="Edit Pengiriman">
                                        Edit
                                    </button>
                                    @if(auth()->user()?->isOwner())
                                        <button type="button"
                                                wire:click="deleteShipment({{ $s->id }})"
                                                wire:confirm="Hapus permanen pengiriman {{ $s->cn_number }} beserta foto POD & log?"
                                                class="px-2 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 text-[11px] font-bold cursor-pointer" title="Hapus (Khusus Owner)">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="px-6 py-10 text-center text-slate-500">
                                Tidak ada data pengiriman yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-extrabold text-[#0F2744] border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="border border-slate-200 px-3 py-2.5 text-left">
                            TOTAL ({{ $stats['total_cn'] }} Pengiriman)
                        </td>
                        <td class="border border-slate-200 px-2 py-2.5 text-right tabular-nums">{{ number_format($stats['total_colly']) }}</td>
                        <td class="border border-slate-200 px-2 py-2.5 text-right tabular-nums">{{ number_format($stats['total_weight'], 2) }}</td>
                        <td class="border border-slate-200 px-2 py-2.5 text-right tabular-nums">{{ number_format($stats['total_volume'], 3) }}</td>
                        <td class="border border-slate-200 px-2 py-2.5 text-right tabular-nums">{{ number_format($stats['total_area'], 2) }}</td>
                        <td colspan="8" class="border border-slate-200 px-3 py-2.5 text-right">Total Tagihan Kirim:</td>
                        <td class="border border-slate-200 px-2.5 py-2.5 text-right tabular-nums">
                            Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                        </td>
                        <td class="print-hide-col border border-slate-200"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- MODAL 1: INPUT / EDIT PENGIRIMAN (CN) - Scrollable body --}}
    @if($showFormModal)
        <div class="no-print fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-[#0F2744] px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-bold">{{ $editingId ? "Edit Pengiriman ({$form_cn_number})" : 'Input Pengiriman Barang Baru (No. CN)' }}</h3>
                        <p class="text-xs text-sky-200/80">Lengkapi rute, spesifikasi muatan, kalkulator dimensi/tarif, dan status pengiriman.</p>
                    </div>
                    <button type="button" wire:click="$set('showFormModal', false)" class="text-slate-300 hover:text-white text-2xl leading-none cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveShipment" class="flex-1 min-h-0 flex flex-col overflow-hidden">
                    <div class="flex-1 min-h-0 overflow-y-auto p-6 space-y-5">
                        {{-- Row 1: CN & Date --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. CN *</label>
                                <input type="text" wire:model="form_cn_number" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-bold uppercase focus:ring-2 focus:ring-sky-500 outline-none">
                                @error('form_cn_number') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengiriman (WIB) *</label>
                                <input type="date" wire:model="form_shipment_date" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                            </div>
                        </div>

                        {{-- Row 2: Customer --}}
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih dari Master Customer (Opsional)</label>
                                    <select wire:model.live="form_customer_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                        <option value="">-- Ketik Manual / Pilih Customer --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->customer_code }} — {{ $c->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Customer *</label>
                                    <input type="text" wire:model="form_customer_name" placeholder="Nama PT / CV / Pengirim" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                    @error('form_customer_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="form_save_customer" class="rounded border-slate-300 text-sky-600">
                                Otomatis simpan ke Master Customer jika nama belum terdaftar
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Pengirim</label>
                                    <textarea wire:model="form_shipper_address" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Penerima / Tujuan</label>
                                    <textarea wire:model="form_receiver_address" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Row 3: Cargo + Alpine Dimension Calculator --}}
                        <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-200 space-y-3" x-data="dimensionCalc">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="text-xs font-extrabold text-[#0F2744] uppercase">Spesifikasi Muatan</div>
                                <button type="button" @click="open = !open"
                                        class="px-3 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold cursor-pointer">
                                    <span x-text="open ? 'Tutup Kalkulator Dimensi' : 'Kalkulator Dimensi (P×L×T)'"></span>
                                </button>
                            </div>

                            <div x-show="open" x-transition class="p-3.5 rounded-xl bg-white border border-sky-200 space-y-3">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Panjang (cm)</label>
                                        <input type="number" step="0.1" x-model="lengthCm" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Lebar (cm)</label>
                                        <input type="number" step="0.1" x-model="widthCm" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tinggi (cm)</label>
                                        <input type="number" step="0.1" x-model="heightCm" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Qty Koli</label>
                                        <input type="number" min="1" x-model="qty" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <div class="text-slate-700">
                                        Hasil: <strong x-text="volM3.toFixed(4)"></strong> m&sup3; &bull;
                                        <strong x-text="areaM2.toFixed(4)"></strong> m&sup2; &bull;
                                        Berat Vol (/4000): <strong x-text="volWeightKg.toFixed(2)"></strong> Kg
                                    </div>
                                    <button type="button" @click="applyToWire($wire)"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold cursor-pointer">
                                        Terapkan ke Form
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Colly</label>
                                    <input type="number" min="0" wire:model="form_colly" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Berat (Kg)</label>
                                    <input type="number" step="0.01" min="0" wire:model="form_weight_kg" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Vol (m&sup3;)</label>
                                    <input type="number" step="0.0001" min="0" wire:model="form_volume_m3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Luas (m&sup2;)</label>
                                    <input type="number" step="0.0001" min="0" wire:model="form_area_m2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                </div>
                            </div>
                        </div>

                        {{-- Row 4: Fleet & Pickup --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Armada</label>
                                <select wire:model.live="form_fleet_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                    <option value="">-- Manual / Pilih Armada --</option>
                                    @foreach($fleets as $f)
                                        <option value="{{ $f->id }}">{{ $f->vehicle_type }} ({{ $f->plate_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Armada / Nopol</label>
                                <input type="text" wire:model="form_fleet_name" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jam Pickup</label>
                                <input type="text" wire:model="form_pickup_time" placeholder="08:30 WIB" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                        </div>

                        {{-- Row 5: Alpine Tariff Calculator & Payment --}}
                        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-3" x-data="tariffCalc">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="text-xs font-extrabold text-amber-950 uppercase">Harga Pengiriman &amp; Kalkulator Tarif</div>
                                <div x-show="basis !== 'manual'" class="text-xs font-bold text-amber-900 bg-amber-100 px-2.5 py-1 rounded-lg border border-amber-300">
                                    Estimasi Kalkulator: <span x-text="formatRupiah(computeTotal($wire.form_colly, $wire.form_weight_kg, $wire.form_volume_m3, $wire.form_area_m2))"></span>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Dasar Hitung Tarif</label>
                                    <select x-model="basis" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                        <option value="manual">Input Harga Manual</option>
                                        <option value="kg">Per Kilogram (Kg)</option>
                                        <option value="volume">Per Kubikasi (m&sup3;)</option>
                                        <option value="colly">Per Colly / Koli</option>
                                        <option value="area">Per Luas (m&sup2;)</option>
                                    </select>
                                </div>
                                <div x-show="basis !== 'manual'">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tarif Satuan (Rp)</label>
                                    <input type="number" x-model="rate" placeholder="Contoh: 5000" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                </div>
                                <div x-show="basis !== 'manual'">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Biaya Tambahan (Rp)</label>
                                    <input type="number" x-model="extraFee" placeholder="0" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                </div>
                                <div x-show="basis !== 'manual'">
                                    <button type="button" @click="applyToWire($wire)"
                                            class="w-full px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold cursor-pointer">
                                        Hitung &amp; Terapkan
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Kirim Final (Rp)</label>
                                    <input type="number" min="0" wire:model="form_service_cost" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-extrabold bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Pembayaran</label>
                                    <select wire:model="form_payment_status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                        @foreach($paymentOptions as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Row 6: Status 1-5, Basic POD & Notes --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status 1 (Pickup)</label>
                                <input type="text" wire:model="form_status_1" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status 2 (Hub Sortir)</label>
                                <input type="text" wire:model="form_status_2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status 3 (Transit)</label>
                                <input type="text" wire:model="form_status_3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status 4 (Delivery)</label>
                                <input type="text" wire:model="form_status_4" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status 5 (Selesai / Delivered)</label>
                                <input type="text" wire:model="form_status_5" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-emerald-900 mb-1">Nama Penerima POD (Opsional)</label>
                                <input type="text" wire:model="form_pod_receiver_name" placeholder="Kosongkan jika belum diterima" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-emerald-900 mb-1">Waktu Terima POD</label>
                                <input type="text" wire:model="form_pod_received_at" placeholder="Otomatis jika dikosongkan" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-emerald-900 mb-1">Catatan POD Singkat</label>
                                <input type="text" wire:model="form_pod_notes" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Operasional</label>
                            <textarea wire:model="form_notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" wire:click="$set('showFormModal', false)"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-sm font-bold shadow cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveShipment">{{ $editingId ? 'Simpan Perubahan' : 'Simpan Pengiriman' }}</span>
                            <span wire:loading wire:target="saveShipment">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: QUICK UPDATE STAGE 1-5 --}}
    @if($showStageModal)
        <div class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-[#0F2744] px-5 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-bold">Update Status {{ $stageNumber }} — {{ $stageShipmentCn }}</h3>
                        <p class="text-[11px] text-sky-200">{{ $stageLabels[$stageNumber] ?? '' }}</p>
                    </div>
                    <button type="button" wire:click="$set('showStageModal', false)" class="text-slate-300 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveStage" class="p-5 space-y-4 overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Template Cepat (Waktu WIB Otomatis)</label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach([
                                'Barang dijemput di lokasi pengirim',
                                'Masuk Hub Sortir AACL Cargo',
                                'Armada berangkat menuju kota tujuan',
                                'Kurir dalam perjalanan ke alamat penerima',
                                'Barang diterima lengkap & aman'
                            ] as $tpl)
                                <button type="button" wire:click="fillStageTemplate('{{ $tpl }}')"
                                        class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-900 text-[11px] font-semibold cursor-pointer">
                                    + {{ $tpl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan &amp; Waktu Status</label>
                        <textarea wire:model="stageValue" rows="3" placeholder="Contoh: 11 Nov 14:30 WIB - Armada berangkat..."
                                  class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 outline-none"></textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2">
                        <button type="button" wire:click="clearStage"
                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold cursor-pointer">
                            Kosongkan Status Ini
                        </button>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('showStageModal', false)"
                                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold cursor-pointer">Batal</button>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs font-bold cursor-pointer">Simpan Status</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 3: QUICK POD + UPLOAD FOTO + TTD DIGITAL --}}
    @if($showPodModal)
        <div class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-emerald-800 px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-bold">Bukti Diterima (POD) — {{ $podShipmentCn }}</h3>
                        <p class="text-xs text-emerald-100">Menyimpan POD otomatis menandai Status 5 (Delivered) jika masih kosong.</p>
                    </div>
                    <button type="button" wire:click="$set('showPodModal', false)" class="text-emerald-200 hover:text-white text-2xl cursor-pointer">&times;</button>
                </div>

                <form wire:submit="savePod" class="flex-1 min-h-0 flex flex-col overflow-hidden">
                    <div class="flex-1 min-h-0 overflow-y-auto p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penerima Barang *</label>
                                <input type="text" wire:model="pod_receiver_name" placeholder="Contoh: Bpk. Agus (Security)"
                                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                                @error('pod_receiver_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Diterima (WIB)</label>
                                <input type="text" wire:model="pod_received_at" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Serah Terima</label>
                            <textarea wire:model="pod_notes" rows="2" placeholder="Kondisi barang saat diterima..."
                                      class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Upload Foto Bukti Penerimaan (Max 5MB)</label>
                            <input type="file" wire:model="pod_photo_file" accept="image/*"
                                   class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            @error('pod_photo_file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror

                            <div class="mt-2 flex flex-wrap gap-3 items-center">
                                @if($pod_photo_file)
                                    <div>
                                        <span class="block text-[10px] font-bold text-emerald-700 mb-1">Preview Foto Baru:</span>
                                        <img src="{{ $pod_photo_file->temporaryUrl() }}" class="h-24 rounded-xl border border-emerald-300 object-cover">
                                    </div>
                                @elseif($pod_existing_photo_url)
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Foto POD Tersimpan:</span>
                                        <img src="{{ $pod_existing_photo_url }}" class="h-24 rounded-xl border border-slate-300 object-cover">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div wire:ignore wire:key="pod-sig-{{ $podShipmentId }}" x-data="signaturePad(@js($pod_signature_data))" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700">
                                    Tanda Tangan Digital Penerima
                                    <span x-show="hasDrawn" class="ml-1.5 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px]">TTD Siap Disimpan</span>
                                </label>
                                <button type="button" @click="clear($wire)"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold cursor-pointer">
                                    Bersihkan TTD
                                </button>
                            </div>
                            <canvas x-ref="canvas" width="560" height="170"
                                    @mousedown="start($event)" @mousemove="move($event)" @mouseup="end($wire)" @mouseleave="end($wire)"
                                    @touchstart.prevent="start($event)" @touchmove.prevent="move($event)" @touchend="end($wire)"
                                    class="w-full h-40 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl touch-none cursor-crosshair"></canvas>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" wire:click="$set('showPodModal', false)"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs sm:text-sm font-bold cursor-pointer">Batal</button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white text-xs sm:text-sm font-bold shadow cursor-pointer disabled:opacity-50">
                            Simpan POD &amp; Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 4: QUICK PRICING & PAYMENT --}}
    @if($showPricingModal)
        <div class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
                <div class="bg-[#0F2744] px-5 py-4 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Harga &amp; Status Bayar — {{ $pricingShipmentCn }}</h3>
                    <button type="button" wire:click="$set('showPricingModal', false)" class="text-slate-300 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="savePricing" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Pengiriman (Rp)</label>
                        <input type="number" min="0" wire:model="pricing_service_cost" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-extrabold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Pembayaran</label>
                        <select wire:model="pricing_payment_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-white">
                            @foreach($paymentOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showPricingModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F2744] text-white text-xs font-bold cursor-pointer">Simpan Harga</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 5: IMPORT CSV --}}
    @if($showImportModal)
        <div class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden">
                <div class="bg-[#0F2744] px-6 py-4 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-bold">Import Data CSV (Template AACL)</h3>
                        <p class="text-xs text-sky-200">Mendukung upload file .csv atau tempel langsung isi CSV 20 kolom.</p>
                    </div>
                    <button type="button" wire:click="$set('showImportModal', false)" class="text-slate-300 hover:text-white text-2xl cursor-pointer">&times;</button>
                </div>
                <form wire:submit="runCsvImport" class="p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih File .csv (Opsional)</label>
                        <input type="file" wire:model="csvFile" accept=".csv,text/csv" class="block w-full text-xs text-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Atau Tempel Isi CSV di Sini</label>
                        <textarea wire:model="csvText" rows="8" placeholder="No. CN,Tanggal,Nama Cust,..."
                                  class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showImportModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer">Proses Import CSV</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 6: PRINT SURAT JALAN / RESI CN (A4 PORTRAIT) --}}
    @if($showPrintModal && $printData)
        <div class="print-cn-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden print-cn-only">
                <div class="no-print bg-[#0F2744] px-6 py-3.5 text-white flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-4">
                        <h3 class="text-sm font-bold">Pratinjau Surat Jalan / Resi {{ $printData['cn_number'] }}</h3>
                        <label class="inline-flex items-center gap-1.5 text-xs text-sky-200 cursor-pointer">
                            <input type="checkbox" wire:model.live="includePriceInPrint" class="rounded text-sky-500">
                            Sertakan Harga Kirim di Cetakan
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="window.aaclPrintCn()"
                                class="px-4 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-bold cursor-pointer">
                            Cetak (A4 Portrait)
                        </button>
                        <button type="button" wire:click="$set('showPrintModal', false)" class="text-slate-300 hover:text-white text-2xl leading-none cursor-pointer">&times;</button>
                    </div>
                </div>

                <div id="cnPrintDocument" class="p-6 sm:p-8 overflow-y-auto space-y-5 text-slate-900">
                    <div class="border-b-2 border-[#0F2744] pb-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xl font-extrabold text-[#0F2744]">{{ $company?->company_name ?: 'PT. AACL Cargo & Logistics' }}</div>
                            <div class="text-xs text-slate-600">{{ $company?->company_address ?: 'Jl. Raya Cakung Cilincing KM 3.5, Jakarta Utara' }}</div>
                            <div class="text-xs text-slate-600">Telp: {{ $company?->company_phone ?: '(021) 4608-8990' }} &bull; {{ $company?->company_email ?: 'ops@aacl-cargo.co.id' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] font-bold uppercase text-slate-500">Surat Jalan / Resi CN</div>
                            <div class="text-xl font-extrabold text-[#0F2744]">{{ $printData['cn_number'] }}</div>
                            <div class="text-xs text-slate-600">Tanggal: {{ $printData['shipment_date'] }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-xl border border-slate-300">
                            <div class="font-bold uppercase text-[10px] text-slate-500">Pengirim / Customer</div>
                            <div class="font-bold text-sm mt-1">{{ $printData['customer_name'] }}</div>
                            <div class="text-slate-700 mt-1">{{ $printData['shipper_address'] ?: '-' }}</div>
                        </div>
                        <div class="p-3.5 rounded-xl border border-slate-300">
                            <div class="font-bold uppercase text-[10px] text-slate-500">Penerima / Alamat Tujuan</div>
                            <div class="text-slate-800 mt-1 font-medium">{{ $printData['receiver_address'] ?: '-' }}</div>
                        </div>
                    </div>

                    <table class="w-full border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="border border-slate-300 px-3 py-2 text-center">Colly</th>
                                <th class="border border-slate-300 px-3 py-2 text-center">Berat (Kg)</th>
                                <th class="border border-slate-300 px-3 py-2 text-center">Volume (m&sup3;)</th>
                                <th class="border border-slate-300 px-3 py-2 text-center">Luas (m&sup2;)</th>
                                <th class="border border-slate-300 px-3 py-2 text-left">Armada &amp; Jam Pickup</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-300 px-3 py-2 text-center font-bold">{{ $printData['colly'] }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-center font-bold">{{ $printData['weight_kg'] }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-center font-bold">{{ $printData['volume_m3'] }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-center font-bold">{{ $printData['area_m2'] }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ $printData['fleet_name'] ?: '-' }} ({{ $printData['pickup_time'] ?: '-' }})</td>
                            </tr>
                        </tbody>
                    </table>

                    @if($includePriceInPrint)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-300 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-600">Biaya / Harga Kirim:</span>
                                <span class="text-base font-extrabold text-[#0F2744] ml-2">Rp {{ number_format((float) $printData['service_cost'], 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-slate-600">Status Pembayaran:</span>
                                <span class="font-extrabold ml-1">{{ $printData['payment_status'] ?: 'Belum Lunas' }}</span>
                            </div>
                        </div>
                    @endif

                    @if(!empty($printData['notes']))
                        <div class="text-xs border border-slate-200 rounded-xl p-3">
                            <strong>Catatan:</strong> {{ $printData['notes'] }}
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-6 pt-4 text-center text-xs">
                        <div>
                            <div class="font-bold text-slate-600 mb-14">Petugas / Admin AACL</div>
                            <div class="border-t border-slate-400 pt-1">( __________________ )</div>
                        </div>
                        <div>
                            <div class="font-bold text-slate-600 mb-14">Driver / Kurir</div>
                            <div class="border-t border-slate-400 pt-1">( __________________ )</div>
                        </div>
                        <div>
                            <div class="font-bold text-slate-600 mb-2">Penerima Barang (POD)</div>
                            @if(!empty($printData['pod_signature_data']))
                                <img src="{{ $printData['pod_signature_data'] }}" class="h-12 mx-auto object-contain mb-1">
                            @else
                                <div class="h-12"></div>
                            @endif
                            <div class="border-t border-slate-400 pt-1">{{ $printData['pod_receiver_name'] ?: '( __________________ )' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
