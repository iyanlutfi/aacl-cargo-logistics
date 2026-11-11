<?php

namespace App\Http\Controllers;

use App\Services\CsvService;
use App\Services\ExcelExportService;
use App\Services\ShipmentDomainService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function exportXlsx(
        Request $request,
        ShipmentDomainService $domain,
        ExcelExportService $excel
    ): Response {
        $filters = $request->only(['search', 'stage', 'date_from', 'date_to', 'payment_status']);
        $shipments = $domain->filterShipmentsQuery($filters)->get();

        $summaryParts = [];
        if (! empty($filters['stage']) && $filters['stage'] !== 'all') {
            $summaryParts[] = 'Stage '.$filters['stage'];
        }
        if (! empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $summaryParts[] = 'Bayar: '.$filters['payment_status'];
        }
        if (! empty($filters['search'])) {
            $summaryParts[] = 'Cari: '.$filters['search'];
        }

        $binary = $excel->buildXlsx($shipments, [
            'summary' => $summaryParts ? implode(', ', $summaryParts) : 'Semua Data',
        ]);

        $filename = 'AACL_Operasional_'.$domain->localIsoDate().'.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function exportCsv(
        Request $request,
        ShipmentDomainService $domain,
        CsvService $csv
    ): Response {
        $filters = $request->only(['search', 'stage', 'date_from', 'date_to', 'payment_status']);
        $shipments = $domain->filterShipmentsQuery($filters)->get();
        $content = $csv->exportCsv($shipments);
        $filename = 'AACL_Operasional_'.$domain->localIsoDate().'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
