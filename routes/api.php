<?php

use App\Http\Controllers\SoapController;
use App\Http\Middleware\Cors;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/soap/wsdl', function () {
    return response()->file(storage_path('wsdl/soap-service.wsdl'));
});

Route::any('/soap', [SoapController::class, 'handleSoapRequest'])->middleware([StartSession::class, 'cors']);