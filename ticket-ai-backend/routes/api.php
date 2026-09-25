<?php
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketController::class, 'index']);
Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:20,1');
Route::post('/tickets/{ticket}/reanalyze', [TicketController::class, 'reanalyze'])->middleware('throttle:20,1');
Route::get('/tickets/{ticket}/similar', [TicketController::class, 'similar']);
