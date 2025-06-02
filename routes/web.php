<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return to_route('install.completed');
});

// Clear Cache
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('clear-compiled');
    Artisan::call('storage:link');
});

Route::get('order/invoice/{order_number}', 'App\Http\Controllers\OrderController@getInvoice')->name('invoice');

// access to ressource puisque on'a mis le backend sous subdirectory admin
Route::get('/admin/storage/{file}', function ($file) {
    $path = storage_path('app/public/' . $file);

    if (file_exists($path)) {
        return response()->file($path);
    }

    abort(404);
});
