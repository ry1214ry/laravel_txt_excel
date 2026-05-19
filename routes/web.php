<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TxtToExcelController;

Route::get('/', [TxtToExcelController::class, 'index'])->name('home');
Route::post('/upload', [TxtToExcelController::class, 'upload'])->name('upload');
Route::get('/download/{filename}', [TxtToExcelController::class, 'download'])->name('download');
Route::delete('/delete/{filename}', [TxtToExcelController::class, 'delete'])->name('delete');