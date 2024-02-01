<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authenticated
Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::group(['prefix' => 'user', 'controller' => UserController::class], function() {
        Route::get('/', 'index');
    });

    Route::group(['prefix' => 'note', 'controller' => NoteController::class], function() {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
});

require __DIR__.'/auth.php';