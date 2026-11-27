<?php

use App\Http\Controllers\StaffServiceStickerController;
use App\Livewire\MarketplaceCatalog;
use App\Livewire\PcBuilderWorkbench;
use App\Livewire\ProductShow;
use App\Livewire\ServiceCenterPage;
use Illuminate\Support\Facades\Route;

Route::get('/', MarketplaceCatalog::class)->name('home');
Route::get('/barang/{slug}', ProductShow::class)->name('product.show');
Route::get('/rakit-pc', PcBuilderWorkbench::class)->name('pc-builder');
Route::get('/servis', ServiceCenterPage::class)->name('service.index');

Route::middleware('auth')->group(function () {
    Route::get('/staf/tiket/{ticket}/stiker-label', [StaffServiceStickerController::class, 'show'])
        ->name('staff.service-sticker');
});
