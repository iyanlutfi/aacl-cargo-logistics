<?php

use App\Http\Controllers\ExportController;
use App\Livewire\DriverPanel;
use App\Livewire\MasterData;
use App\Livewire\OwnerUsers;
use App\Livewire\PublicTracking;
use App\Livewire\ShipmentBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicTracking::class)->name('public.tracking');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('public.tracking')->with('success', 'Anda telah keluar.');
})->name('logout');

Route::middleware(['role:owner,admin'])->group(function () {
    Route::get('/operasional', ShipmentBoard::class)->name('operasional');
    Route::get('/master', MasterData::class)->name('master');
    Route::get('/export/xlsx', [ExportController::class, 'exportXlsx'])->name('export.xlsx');
    Route::get('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');
});

Route::middleware(['role:owner,driver'])->group(function () {
    Route::get('/kurir', DriverPanel::class)->name('kurir');
});

Route::middleware(['role:owner'])->group(function () {
    Route::get('/owner', OwnerUsers::class)->name('owner');
});
