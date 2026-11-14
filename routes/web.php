<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ClientController;

Auth::routes();

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');

    // Welcome
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');

    // Clients
    Route::resource('clients', ClientController::class)
    ->middlewareFor('index', 'permission:clients-list')
    ->middlewareFor(['create', 'store'], 'permission:clients-create')
    ->middlewareFor('show', 'permission:clients-view')
    ->middlewareFor(['edit', 'update'], 'permission:clients-edit')
    ->middlewareFor('destroy', 'permission:clients-delete');
});
