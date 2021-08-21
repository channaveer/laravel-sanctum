<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::group(["prefix" => "auth", "as" => "auth."], function () {
    Route::post("/authenticate", [LoginController::class, "authenticate"])->name("authenticate");
    Route::post("/register", [RegisterController::class, "register"])->name("register");
    Route::post("/forgot-password", [ForgotPasswordController::class, "forgotPassword"])->name("forgot-password");
    Route::post("/account-verification", [EmailVerificationController::class, "accountVerification"])->name("account-verification");
    Route::post("/verify-reset-password", [ResetPasswordController::class, "verifyResetPassword"])->name("verify-reset-password");
    Route::patch("/reset-password", [ResetPasswordController::class, "resetPassword"])->name("reset-password");
    Route::middleware("auth:sanctum")->group(function () {
        Route::post("/logout", [LoginController::class, "logout"])->name("logout");
    });
});

Route::middleware("auth:sanctum")->group(function () {
    /** User Routes */
    Route::group(["prefix" => "users", "as" => "users."], function () {
        Route::get("/show", [UserController::class, "show"])->name("show");
    });
});
