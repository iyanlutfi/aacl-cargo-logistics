<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AaclCargoSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::firstOrCreate(
            ['cn_prefix' => 'AACL'],
            [
                'company_name' => 'PT. AACL Cargo & Logistics',
                'company_tagline' => 'Integrated Freight, Trucking & Project Cargo Solutions',
                'company_address' => 'Jl. Raya Cakung Cilincing KM 3.5, Jakarta Utara 14130',
                'company_phone' => '(021) 4608-8990',
                'company_email' => 'ops@aacl-cargo.co.id',
            ]
        );

        $users = [
            [
                'username' => 'owner',
                'name' => 'Owner AACL Cargo',
                'full_name' => 'Owner AACL Cargo',
                'email' => 'owner@aacl-cargo.local',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'phone' => '0811-1000-001',
            ],
            [
                'username' => 'admin',
                'name' => 'Admin Operasional',
                'full_name' => 'Admin Operasional',
                'email' => 'admin@aacl-cargo.local',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '0811-1000-002',
            ],
            [
                'username' => 'kurir',
                'name' => 'Budi Santoso',
                'full_name' => 'Budi Santoso (Kurir)',
                'email' => 'kurir@aacl-cargo.local',
                'password' => Hash::make('kurir123'),
                'role' => 'driver',
                'phone' => '0812-8800-101',
            ],
            [
                'username' => 'dedi',
                'name' => 'Dedi Kurniawan',
                'full_name' => 'Dedi Kurniawan (Driver)',
                'email' => 'dedi@aacl-cargo.local',
                'password' => Hash::make('kurir123'),
                'role' => 'driver',
                'phone' => '0812-8800-102',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['username' => $u['username']], $u);
        }

        $customers = [
            [
                'customer_code' => 'CUST-001',
                'company_name' => 'PT. Sinar Baja Elektronika',
                'pic_name' => 'Bpk. Hendra Wijaya',
                'phone' => '021-5559811',
                'default_address' => 'Kawasan Industri Jababeka II Blok C-12, Cikarang',
                'notes' => 'Prioritas pengiriman pagi, wajib surat jalan rangkap 2',
            ],
            [
                'customer_code' => 'CUST-002',
                'company_name' => 'CV. Maju Jaya Packaging',
                'pic_name' => 'Ibu Ratna Sari',
                'phone' => '021-8891234',
                'default_address' => 'Jl. Raya Narogong KM 11, Bantar Gebang, Bekasi',
                'notes' => 'Kontrak TOP 14 Hari',
            ],
            [
                'customer_code' => 'CUST-003',
                'company_name' => 'PT. Karya Mandiri Steel',
                'pic_name' => 'Bpk. Agung Pratama',
                'phone' => '031-7490021',
                'default_address' => 'Kawasan Industri MM2100 Blok M-05, Cibitung',
                'notes' => 'Muatan berat / palletize, perlu forklift tujuan',
            ],
            [
                'customer_code' => 'CUST-004',
                'company_name' => 'PT. Nusantara Medika Farma',
                'pic_name' => 'Ibu Dina Permata',
                'phone' => '022-6034112',
                'default_address' => 'Jl. Soekarno Hatta No. 418, Bandung',
                'notes' => 'Jaga kondisi kering & segel box rapat',
            ],
            [
                'customer_code' => 'CUST-005',
                'company_name' => 'PT. Global Tekstil Indonesia',
                'pic_name' => 'Bpk. Rudi Hartono',
                'phone' => '024-3581990',
                'default_address' => 'Jl. Raya Bitung KM 8, Cikupa, Tangerang',
                'notes' => 'TOP 30 Hari corporate',
            ],
        ];

        $customerMap = [];
        foreach ($customers as $c) {
            $customerMap[$c['customer_code']] = Customer::updateOrCreate(
                ['customer_code' => $c['customer_code']],
                $c
            );
        }

        $fleets = [
            [
                'fleet_code' => 'FLT-001',
                'plate_number' => 'B 9412 UCD',
                'vehicle_type' => 'CDD Box Long',
                'driver_name' => 'Budi Santoso',
                'driver_phone' => '0812-8800-101',
                'status' => 'on_trip',
            ],
            [
                'fleet_code' => 'FLT-002',
                'plate_number' => 'B 9188 KXT',
                'vehicle_type' => 'Fuso Wingbox',
                'driver_name' => 'Dedi Kurniawan',
                'driver_phone' => '0812-8800-102',
                'status' => 'on_trip',
            ],
            [
                'fleet_code' => 'FLT-003',
                'plate_number' => 'B 9024 BDA',
                'vehicle_type' => 'CDE Engkel Box',
                'driver_name' => 'Slamet Riyadi',
                'driver_phone' => '0813-7711-203',
                'status' => 'available',
            ],
            [
                'fleet_code' => 'FLT-004',
                'plate_number' => 'B 9560 TUV',
                'vehicle_type' => 'Pickup GrandMax Box',
                'driver_name' => 'Eko Prasetyo',
                'driver_phone' => '0815-9900-304',
                'status' => 'available',
            ],
            [
                'fleet_code' => 'FLT-005',
                'plate_number' => 'B 9871 WXY',
                'vehicle_type' => 'Tronton Bak Terbuka',
                'driver_name' => 'Joko Susilo',
                'driver_phone' => '0817-6622-505',
                'status' => 'maintenance',
            ],
        ];

        $fleetMap = [];
        foreach ($fleets as $f) {
            $fleetMap[$f['fleet_code']] = Fleet::updateOrCreate(
                ['fleet_code' => $f['fleet_code']],
                $f
            );
        }

        $shipments = [
            [
                'cn_number' => 'AACL-2611-0001',
                'shipment_date' => '2026-11-07',
                'customer_id' => $customerMap['CUST-001']->id,
                'customer_name' => 'PT. Sinar Baja Elektronika',
                'shipper_address' => 'Kawasan Industri Jababeka II Blok C-12, Cikarang',
                'receiver_address' => 'PT. Mega Retail Surabaya, Jl. Rungkut Industri III No. 45, Surabaya',
                'colly' => 24,
                'weight_kg' => 840.5,
                'volume_m3' => 4.25,
                'area_m2' => 3.5,
                'fleet_id' => $fleetMap['FLT-002']->id,
                'fleet_name' => 'Fuso Wingbox (B 9188 KXT)',
                'pickup_time' => '08:30 WIB',
                'service_cost' => 4250000,
                'payment_status' => 'Lunas',
                'status_1_at' => '07 Nov 08:35 - Barang dijemput di gudang Jababeka',
                'status_2_at' => '07 Nov 11:20 - Masuk Hub Sortir Jakarta Timur',
                'status_3_at' => '07 Nov 19:00 - Berangkat via Tol Trans Jawa',
                'status_4_at' => '08 Nov 09:15 - Armada tiba di Pool Surabaya, persiapan kirim',
                'status_5_at' => '08 Nov 13:40 - Diterima lengkap oleh Gudang Rungkut',
                'pod_receiver_name' => 'Suharto (Kepala Gudang)',
                'pod_received_at' => '2026-11-08 13:40 WIB',
                'pod_notes' => '24 Colly utuh, segel aman, surat jalan bertanda tangan.',
                'current_stage' => 5,
                'notes' => 'Pengiriman reguler jalur Pantura / Tol Trans Jawa',
            ],
            [
                'cn_number' => 'AACL-2611-0002',
                'shipment_date' => '2026-11-08',
                'customer_id' => $customerMap['CUST-002']->id,
                'customer_name' => 'CV. Maju Jaya Packaging',
                'shipper_address' => 'Jl. Raya Narogong KM 11, Bantar Gebang, Bekasi',
                'receiver_address' => 'Gudang Logistik Bandung Barat, Padalarang',
                'colly' => 40,
                'weight_kg' => 620,
                'volume_m3' => 5.6,
                'area_m2' => 4.0,
                'fleet_id' => $fleetMap['FLT-001']->id,
                'fleet_name' => 'CDD Box Long (B 9412 UCD)',
                'pickup_time' => '09:15 WIB',
                'service_cost' => 1850000,
                'payment_status' => 'Tagihan TOP 14 Hari',
                'status_1_at' => '08 Nov 09:30 - Pickup karton packaging di Bekasi',
                'status_2_at' => '08 Nov 12:10 - Konsolidasi & timbang ulang di Hub AACL',
                'status_3_at' => '08 Nov 15:45 - Dalam perjalanan menuju Padalarang via Cipularang',
                'status_4_at' => '09 Nov 08:10 - Kurir menuju titik bongkar penerima',
                'status_5_at' => null,
                'current_stage' => 4,
                'notes' => 'Invoice dilampirkan bersama tanda terima',
            ],
            [
                'cn_number' => 'AACL-2611-0003',
                'shipment_date' => '2026-11-09',
                'customer_id' => $customerMap['CUST-003']->id,
                'customer_name' => 'PT. Karya Mandiri Steel',
                'shipper_address' => 'Kawasan Industri MM2100 Blok M-05, Cibitung',
                'receiver_address' => 'Proyek Konstruksi Kawasan Industri Kendal, Jawa Tengah',
                'colly' => 12,
                'weight_kg' => 2450,
                'volume_m3' => 6.8,
                'area_m2' => 5.2,
                'fleet_id' => $fleetMap['FLT-002']->id,
                'fleet_name' => 'Fuso Wingbox (B 9188 KXT)',
                'pickup_time' => '10:00 WIB',
                'service_cost' => 5400000,
                'payment_status' => 'DP / Sebagian',
                'status_1_at' => '09 Nov 10:15 - Muat 12 pallet komponen baja',
                'status_2_at' => '09 Nov 14:00 - Verifikasi dokumen & lashing selesai',
                'status_3_at' => '09 Nov 20:30 - Armada melintas jalur Tol Cikopo-Palimanan',
                'status_4_at' => null,
                'status_5_at' => null,
                'current_stage' => 3,
                'notes' => 'Butuh koordinasi crane/forklift 1 jam sebelum tiba',
            ],
            [
                'cn_number' => 'AACL-2611-0004',
                'shipment_date' => '2026-11-10',
                'customer_id' => $customerMap['CUST-004']->id,
                'customer_name' => 'PT. Nusantara Medika Farma',
                'shipper_address' => 'Jl. Soekarno Hatta No. 418, Bandung',
                'receiver_address' => 'RSUD & Apotek Distribusi Pusat, Jl. Diponegoro No. 88, Jakarta Pusat',
                'colly' => 18,
                'weight_kg' => 310,
                'volume_m3' => 2.1,
                'area_m2' => 1.8,
                'fleet_id' => $fleetMap['FLT-003']->id,
                'fleet_name' => 'CDE Engkel Box (B 9024 BDA)',
                'pickup_time' => '13:00 WIB',
                'service_cost' => 1350000,
                'payment_status' => 'Tagihan TOP 30 Hari',
                'status_1_at' => '10 Nov 13:15 - Barang dijemput di gudang farmasi Bandung',
                'status_2_at' => '10 Nov 16:40 - Masuk staging area & pengecekan segel box',
                'status_3_at' => null,
                'status_4_at' => null,
                'status_5_at' => null,
                'current_stage' => 2,
                'notes' => 'Handling khusus box kering, hindari tumpukan > 4 karton',
            ],
            [
                'cn_number' => 'AACL-2611-0005',
                'shipment_date' => '2026-11-11',
                'customer_id' => $customerMap['CUST-005']->id,
                'customer_name' => 'PT. Global Tekstil Indonesia',
                'shipper_address' => 'Jl. Raya Bitung KM 8, Cikupa, Tangerang',
                'receiver_address' => 'Sentra Garment Tanah Abang Blok F Lt. 3, Jakarta Pusat',
                'colly' => 15,
                'weight_kg' => 450,
                'volume_m3' => 3.0,
                'area_m2' => 2.5,
                'fleet_id' => $fleetMap['FLT-004']->id,
                'fleet_name' => 'Pickup GrandMax Box (B 9560 TUV)',
                'pickup_time' => '08:00 WIB',
                'service_cost' => 650000,
                'payment_status' => 'Belum Lunas',
                'status_1_at' => '11 Nov 08:10 - Barang tekstil dijemput dari pabrik Cikupa',
                'status_2_at' => null,
                'status_3_at' => null,
                'status_4_at' => null,
                'status_5_at' => null,
                'current_stage' => 1,
                'notes' => 'Antar langsung same-day area Jakarta Pusat',
            ],
        ];

        $stageLabels = [
            1 => 'St. 1: Pickup / Barang Dijemput',
            2 => 'St. 2: Proses Hub / Sortir',
            3 => 'St. 3: Transit / Dalam Perjalanan',
            4 => 'St. 4: Out for Delivery / Kurir Menuju Lokasi',
            5 => 'St. 5: Delivered / Diterima & POD',
        ];

        foreach ($shipments as $s) {
            $shipment = Shipment::updateOrCreate(['cn_number' => $s['cn_number']], $s);
            if ($shipment->trackingLogs()->count() === 0) {
                for ($i = 1; $i <= 5; $i++) {
                    $val = $shipment->{"status_{$i}_at"};
                    if (filled($val)) {
                        TrackingLog::create([
                            'shipment_id' => $shipment->id,
                            'stage_number' => $i,
                            'stage_label' => $stageLabels[$i],
                            'description' => $val,
                            'updated_by' => 'System Seeder',
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
