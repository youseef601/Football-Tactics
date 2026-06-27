<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyFatoorahController;

Route::get('/test', function () {
    return view('welcome');
});
