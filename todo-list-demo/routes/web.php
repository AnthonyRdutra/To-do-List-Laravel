<?php

use Illuminate\Support\Facades\Route;

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/notes', 'notes.index')->name('notes.index');

Route::get('/', function () {
    return redirect()->route('login');
});
