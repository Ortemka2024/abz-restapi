<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('users.index');

Route::get('/add-new-user', function () {
    return view('create');
})->name('user.create');
