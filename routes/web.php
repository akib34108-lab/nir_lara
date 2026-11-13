<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Auth::routes();
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');
    Route::get('/', function () {
    return view('welcome');
    })->name('welcome');
});
