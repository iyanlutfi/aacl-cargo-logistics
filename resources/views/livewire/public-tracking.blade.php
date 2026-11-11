<div class="space-y-6">
    {{-- Hero Search Box (Hidden on CN Print) --}}
    <div class="no-print print-hide-on-cn bg-gradient-to-br from-[#0F2744] via-[#17375e] to-[#0d2f56] rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-white/10">
        <div class="max-w-3xl mx-auto text-center space-y-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-400/15 border border-sky-300/30 text-sky-200 text-xs font-bold tracking-wide uppercase">
                Trace &amp; Tracking System
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                Lacak Status Pengiriman Cargo (No. CN)
            </h1>
            <p class="text-sm sm:text-base text-sky-100/80 max-w-xl mx-auto">
                Masukkan Nomor Consignment Note (CN) Anda untuk melihat progres perjalanan armada dan bukti serah terima (POD) secara real-time.
            </p>

            <form wire:submit="searchCn" class="mt-6 flex flex-col sm:flex-row gap-2.5 max-w-xl mx-auto">
                <input type="text"
                       wire:model="cnQuery"
                       placeholder="Masukkan No. CN (contoh: AACL-2611-0001)"
                       class="flex-1 px-4 py-3.5 rounded-xl bg-white text-slate-900 placeholder:text-slate-400 font-semibold text-sm sm:text-base shadow-inner focus:ring-4 focus:ring-sky-400/40 outline-none uppercase">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-3.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-bold text-sm sm:text-base shadow-lg shadow-sky-900/40 transition cursor-pointer shrink-0">
                    <span wire:loading.remove wire:target="searchCn">Lacak Resi</span>
                    <span wire:loading wire:target="searchCn">Mencari...</span>
                </button>
            </form>

            @if($sampleCns->isNotEmpty())
                <div class="pt-2 flex flex-wrap items-center justify-center gap-1.5 text-xs">
                    <span class="text-sky-200/80 mr-1">Coba lacak cepat:</span>
                    @foreach($sampleCns as $sample)
                        <button type="button" wire:click="quickSelectCn('{{ $sample->cn_number }}')"
                                class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-sky-100 border border-white/15 font-mono text-[11px] font-bold transition cursor-pointer">
                            {{ $sample->cn_number }}
                        </button>
                    @endforeach
                </div>
            @endif

            <p class="text-xs text-sky-200/70">
                Butuh informasi pengiriman perusahaan? Hubungi {{ $company?->company_phone ?: '(021) 4608-8990' }}
            </p>
        </div>
    </div>

    {{-- Result Card --}}
    @if($searched && !$result)
        <div class="no-print max-w-2xl mx-auto bg-white rounded-2xl border border-rose-200 p-8 text-center shadow-sm">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 font-extrabold text-xl">!</div>
            <h2 class="text-lg font-bold text-slate-900">Nomor CN "{{ $cnQuery }}" Tidak Ditemukan</h2>
            <p class="text-sm text-slate-600 mt-1">
                Periksa kembali penulisan nomor CN pada surat jalan Anda, atau hubungi tim operasional AACL Cargo.
            </p>
        </div>
    @elseif($result)
        @php
            $stage = (int) ($result['current_stage'] ?? 0);
            $stages = [
                1 => ['title' => 'Pickup', 'desc' => $result['status_1_at'] ?? null],
                2 => ['title' => 'Hub / Sortir', 'desc' => $result['status_2_at'] ?? null],
                3 => ['title' => 'Transit', 'desc' => $result['status_3_at'] ?? null],
                4 => ['title' => 'Out for Delivery', 'desc' => $result['status_4_at'] ?? null],
                5 => ['title' => 'Delivered & POD', 'desc' => $result['status_5_at'] ?? null],
            ];
            $stageBadgeText = $stage >= 5 ? 'Selesai / Delivered' : ($stage === 0 ? 'Terjadwal / Menunggu Pickup' : "Tahap {$stage} dari 5");
        @endphp

        <div class="bg-white rounded-3xl shadow-md border border-slate-200 overflow-hidden print-cn-only">
            {{-- Header --}}
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Consignment Note (Resi Cargo)</div>
                    <div class="flex flex-wrap items-center gap-3 mt-1">
                        <h2 class="text-2xl font-extrabold text-[#0F2744]">{{ $result['cn_number'] }}</h2>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $stage >= 5 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-sky-100 text-sky-800 border border-sky-200' }}">
                            {{ $stageBadgeText }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Tanggal Pengiriman: <strong class="text-slate-700">{{ $result['shipment_date'] }}</strong> &bull; Customer: <strong class="text-slate-700">{{ $result['customer_name'] }}</strong></p>
                </div>

                <button type="button"
                        x-data
                        @click="window.aaclPrintCn()"
                        class="no-print px-4 py-2.5 rounded-xl bg-[#0F2744] hover:bg-[#17375e] text-white text-xs sm:text-sm font-bold shadow cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Cetak Bukti Tracking
                </button>
            </div>

            <div class="p-6 space-y-6">
                {{-- 5-Stage Visual Stepper --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Progres Pengiriman (5 Tahap)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                        @foreach($stages as $num => $st)
                            @php
                                $done = filled($st['desc']);
                                $isCurrent = ($stage === $num);
                            @endphp
                            <div class="rounded-2xl p-3.5 border transition {{ $done ? ($num === 5 ? 'bg-emerald-50 border-emerald-300' : 'bg-sky-50 border-sky-200') : 'bg-slate-50 border-slate-200 opacity-70' }}">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-6 h-6 rounded-full text-xs font-extrabold flex items-center justify-center {{ $done ? ($num === 5 ? 'bg-emerald-600 text-white' : 'bg-[#0F2744] text-white') : 'bg-slate-300 text-slate-700' }}">
                                        {{ $done ? '✓' : $num }}
                                    </span>
                                    @if($isCurrent)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Terkini</span>
                                    @endif
                                </div>
                                <div class="text-xs font-bold text-slate-900">{{ $num }}. {{ $st['title'] }}</div>
                                <div class="text-[11px] text-slate-600 mt-1 leading-snug">
                                    {{ $st['desc'] ?: 'Menunggu pembaruan' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Addresses & Cargo Specs --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] font-bold uppercase text-slate-500">Alamat Pengirim</div>
                        <div class="text-sm font-semibold text-slate-900 mt-1">{{ $result['customer_name'] }}</div>
                        <div class="text-xs text-slate-600 mt-1">{{ $result['shipper_address'] ?: '-' }}</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] font-bold uppercase text-slate-500">Alamat Penerima / Tujuan</div>
                        <div class="text-xs text-slate-800 font-medium mt-1 leading-relaxed">{{ $result['receiver_address'] ?: '-' }}</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] font-bold uppercase text-slate-500">Spesifikasi Muatan &amp; Armada</div>
                        <div class="grid grid-cols-2 gap-y-1 text-xs mt-1.5">
                            <span class="text-slate-500">Jumlah Colly:</span>
                            <span class="font-bold text-slate-900 text-right">{{ number_format($result['colly']) }} Colly</span>
                            <span class="text-slate-500">Berat:</span>
                            <span class="font-bold text-slate-900 text-right">{{ number_format($result['weight_kg'], 2) }} Kg</span>
                            <span class="text-slate-500">Volume / Luas:</span>
                            <span class="font-bold text-slate-900 text-right">{{ number_format($result['volume_m3'], 3) }} m&sup3; / {{ number_format($result['area_m2'], 2) }} m&sup2;</span>
                            <span class="text-slate-500">Armada / Pickup:</span>
                            <span class="font-semibold text-slate-900 text-right">{{ $result['fleet_name'] ?: '-' }} ({{ $result['pickup_time'] ?: '-' }})</span>
                        </div>
                    </div>
                </div>

                {{-- Proof of Delivery (POD) --}}
                @if($result['pod_receiver_name'] || $result['pod_photo_url'] || $result['pod_signature_data'] || $result['pod_notes'])
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-extrabold text-emerald-900">Bukti Serah Terima Barang (Proof of Delivery / POD)</h3>
                            @if($result['pod_received_at'])
                                <span class="text-xs font-semibold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full">
                                    Diterima: {{ $result['pod_received_at'] }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                            <div class="space-y-1 text-xs text-slate-700">
                                <div><span class="text-slate-500">Nama Penerima:</span> <strong>{{ $result['pod_receiver_name'] ?: '-' }}</strong></div>
                                <div><span class="text-slate-500">Catatan POD:</span> {{ $result['pod_notes'] ?: '-' }}</div>
                            </div>

                            @if($result['pod_photo_url'])
                                <div>
                                    <div class="text-[11px] font-bold text-slate-500 mb-1">Foto Bukti Serah Terima</div>
                                    <a href="{{ $result['pod_photo_url'] }}" target="_blank" class="block">
                                        <img src="{{ $result['pod_photo_url'] }}" alt="Foto POD" class="h-32 w-full object-cover rounded-xl border border-slate-200 bg-white">
                                    </a>
                                </div>
                            @endif

                            @if($result['pod_signature_data'])
                                <div>
                                    <div class="text-[11px] font-bold text-slate-500 mb-1">Tanda Tangan Penerima</div>
                                    <div class="bg-white rounded-xl border border-slate-200 p-2 inline-block">
                                        <img src="{{ $result['pod_signature_data'] }}" alt="TTD Penerima" class="h-24 w-auto object-contain">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Tracking Logs --}}
                @if(!empty($result['logs']))
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2.5">Log Aktivitas Pengiriman</h3>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                            @foreach($result['logs'] as $log)
                                <div class="px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 text-xs bg-white">
                                    <div>
                                        <span class="font-bold text-[#0F2744]">{{ $log['stage_label'] }}</span>
                                        <span class="text-slate-600 ml-2">{{ $log['description'] }}</span>
                                    </div>
                                    <span class="text-slate-400 font-mono text-[11px]">
                                        {{ $log['created_at'] }} &bull; {{ $log['updated_by'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Feature Highlights when idle --}}
        <div class="no-print grid grid-cols-1 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
                <div class="text-xs font-bold text-sky-600 uppercase">Real-Time 5 Tahap</div>
                <div class="font-bold text-slate-900 mt-1">Transparansi Penuh</div>
                <p class="text-xs text-slate-600 mt-1">Pantau setiap pergerakan dari penjemputan (Pickup), Hub Sortir, Transit armada, hingga tiba di penerima.</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
                <div class="text-xs font-bold text-emerald-600 uppercase">Digital POD</div>
                <div class="font-bold text-slate-900 mt-1">Foto &amp; Tanda Tangan Digital</div>
                <p class="text-xs text-slate-600 mt-1">Kurir mengunggah foto serah terima dan tanda tangan digital penerima langsung dari lokasi bongkar.</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
                <div class="text-xs font-bold text-amber-600 uppercase">B2B Cargo</div>
                <div class="font-bold text-slate-900 mt-1">Armada Terdedikasi</div>
                <p class="text-xs text-slate-600 mt-1">Mendukung muatan Colly, Berat (Kg), Kubikasi (m&sup3;), serta armada Pickup hingga Fuso Wingbox &amp; Tronton.</p>
            </div>
        </div>
    @endif
</div>
