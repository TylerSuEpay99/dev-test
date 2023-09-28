<?php

use Illuminate\Support\Facades\Route;

//新增controller
use App\Http\Controllers\HelloController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\CreateTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', [HelloController::class,'index']);

Route::get('/info', [InfoController::class,'index']);

Route::get('/createtest', [CreateTestController::class,'index']);
