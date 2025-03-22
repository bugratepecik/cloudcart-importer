<?php

use App\Http\Controllers\CloudCartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', function () {
    return view('test');
});

Route::get('/cloudcart/upload', [CloudCartController::class, 'showUploadForm']);
Route::post('/cloudcart/upload', [CloudCartController::class, 'handleUpload']);
