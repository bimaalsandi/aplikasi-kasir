<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin/login');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/receipt/{id}', function ($id) {
        $transaction = Transaction::with(['transaction_detail', 'user'])->findOrFail($id);

        return view('receipt', compact('transaction'));
    })->name('receipt.print');
});
