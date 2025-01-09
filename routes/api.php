<?php

use App\Http\Controllers\Api\V1\PositionController as PositionControllerV1;
use App\Http\Controllers\Api\V1\TokenController as TokenControllerV1;
use App\Http\Controllers\Api\V1\UserController as UserControllerV1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    Route::get('/token', [TokenControllerV1::class, 'generateToken'])->name('token.generate');

    Route::get('/positions', [PositionControllerV1::class, 'index'])->name('positions');

    Route::prefix('users')->name('users.')->controller(UserControllerV1::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{userId}', 'show')->name('show');
        Route::post('/', 'store')->middleware('validate.token')->name('store');
    });
});
