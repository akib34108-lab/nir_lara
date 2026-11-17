<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserListController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

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

    Route::resource('users-list', UserListController::class)
    ->middlewareFor('index', 'permission:users-list');
    Route::put('users-list/{user}/role', [UserListController::class, 'assignRole'])
    ->middleware('permission:users-edit')
    ->name('users-list.assignRole');

    Route::resource('roles', RoleController::class)
    ->middlewareFor('index', 'permission:roles-list')
    ->middlewareFor(['create', 'store'], 'permission:roles-create')
    ->middlewareFor('show', 'permission:roles-view')
    ->middlewareFor(['edit', 'update'], 'permission:roles-edit')
    ->middlewareFor('destroy', 'permission:roles-delete');

    Route::resource('permissions', PermissionController::class)
    ->except(['create', 'edit']);
});
