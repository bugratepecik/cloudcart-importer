<?php

use App\Http\Controllers\CloudCartController;
use Illuminate\Support\Facades\Route;

Route::post('/cloudcart/upload-csv', [CloudCartController::class, 'handleApiUpload']);
