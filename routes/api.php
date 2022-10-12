<?php

use App\Http\Controllers\API\fileUploads;
use App\Http\Controllers\API\userController;
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
// Route::get('/uploads', [FileUpload::class, 'createForm']);
Route::post('/Post',[userController::class, 'Post'])->name('Post');

Route::get('/getPost',[userController::class, 'getPost']);

Route::post('/upload-files', [fileUploads::class, 'fileUploads'])->name('fileUploads');

