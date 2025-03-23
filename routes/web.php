<?php

use App\Http\Controllers\CloudCartController;
use Illuminate\Support\Facades\Route;


Route::get('/cloudcart/upload', [CloudCartController::class, 'showUploadForm']);
Route::post('/cloudcart/upload', [CloudCartController::class, 'handleUpload']);
