<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('register', RegisterController::class)->name('register');
        Route::post('login', LoginController::class)->name('login');
        Route::middleware('auth:sanctum')->group(function(){
            Route::post('logout', LogoutController::class)->name('logout');
            Route::get('me',MeController::class)->name('me');
            Route::post('reset_password',ResetPasswordController::class)->name('reset_password');
        });
    });
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('poll',[PollController::class,'store']);
        Route::get('poll',[PollController::class,'index']);
        Route::get('poll/{id}',[PollController::class,'indexId']);
        Route::delete('poll/{id}',[PollController::class,'Delete']);
        Route::post('poll/{poll_id}/vote/{choice_id}',VoteController::class)->name('vote');
    });
});
