<?php

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
    return view('welcome');
});

Route::get('wallets', 'WalletsController@index');
Route::post('add-wallet', 'WalletsController@addWallet');
Route::post('delete-wallet', 'WalletsController@deleteWallet');

Route::get('transactions', 'TransactionsController@index');
Route::post('getTransactionsEach10secFromPage', 'TransactionsController@getTransactionsEach10secFromPage');