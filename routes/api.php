<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MediaFolderController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ShareController;


Route::post('/user/login', [AuthController::class, 'openSession'])->name('login');
Route::post('/user/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('/user/logout', [AuthController::class, 'closeSession'])->middleware('auth:sanctum');
Route::post('/token/verify', [AuthController::class, 'verifyToken']);
Route::post('/user/register', [RegisterController::class, 'createAccount']);


Route::post('/user/forgot-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('/user/reset-passord', [ProfileController::class, 'asResetedPassword'])->middleware('auth:sanctum');;
Route::post('/update/password', [ProfileController::class, 'updateUserPassword'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', [AuthController::class, 'showAuthUSer']);

    Route::apiResource('/admin/company', CompanyController::class);

    # Dossiers en points
    Route::apiResource('folders', MediaFolderController::class);

    # Fichiers en points
    Route::apiResource('files', MediaFileController::class)->except(['update']);
    Route::post('files/upload', [MediaFileController::class, 'store']);

    # Templates en points
    Route::apiResource('templates', TemplateController::class)->except(['destroy']);

    # Share en points
    Route::post('files/{file}/share', [ShareController::class, 'shareMedia']);
    Route::get('/shares/all', [ShareController::class, 'listAllShares']);
});
