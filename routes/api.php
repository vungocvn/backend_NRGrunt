<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
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

Route::middleware(['api'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/', [App\Http\Controllers\ProductController::class, 'getAll']);
        Route::get('/{id}', [App\Http\Controllers\ProductController::class, 'getById']);
        Route::post('/', [App\Http\Controllers\ProductController::class, 'create']);
        Route::delete('/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);
        Route::put('/{id}', [App\Http\Controllers\ProductController::class, 'update']);
        Route::put('/change-status/{id}', [App\Http\Controllers\ProductController::class, 'changeStatus']);
        Route::post('/upload-image', [App\Http\Controllers\ProductController::class, 'uploadImage']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [App\Http\Controllers\CategoryController::class, 'getAll']);
        Route::get('/{id}', [App\Http\Controllers\CategoryController::class, 'getById']);
        Route::post('/', [App\Http\Controllers\CategoryController::class, 'create']);
        Route::delete('/{id}', [App\Http\Controllers\CategoryController::class, 'destroy']);
        Route::put('/{id}', [App\Http\Controllers\CategoryController::class, 'update']);
    });

    Route::prefix('carts')->group(function () {
        Route::get('/', [App\Http\Controllers\CartController::class, 'getAll']);
        Route::get('/{id}', [App\Http\Controllers\CartController::class, 'getById']);
        Route::post('/', [App\Http\Controllers\CartController::class, 'create']);
        Route::delete('/{id}', [App\Http\Controllers\CartController::class, 'destroy']);
        Route::put('/{id}', [App\Http\Controllers\CartController::class, 'update']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [App\Http\Controllers\OrderController::class, 'getAll']);
        Route::get('/my-orders', [App\Http\Controllers\OrderController::class, 'getMyOrders']);
        Route::get('/{id}', [App\Http\Controllers\OrderController::class, 'getById']);
        Route::post('/', [App\Http\Controllers\OrderController::class, 'create']);
        Route::delete('/{id}', [App\Http\Controllers\OrderController::class, 'destroy']);
        Route::put('/{id}', [App\Http\Controllers\OrderController::class, 'update']);
        Route::post('/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancel']);
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [App\Http\Controllers\NotifiController::class, 'getAll']);
        Route::get('/{id}', [App\Http\Controllers\NotifiController::class, 'getById']);
        Route::post('/', [App\Http\Controllers\NotifiController::class, 'create']);
        // Route::delete('/{id}', [App\Http\Controllers\NotifiController::class, 'destroy']);
        // Route::put('/{id}', [App\Http\Controllers\NotifiController::class, 'update']);
    });

    Route::prefix('detail-orders')->group(function () {
        Route::get('/', [App\Http\Controllers\DetailOrderController::class, 'getAll']);
    });

    Route::prefix('sale-reports')->group(function () {
        Route::get('/', [App\Http\Controllers\SaleReportController::class, 'getAll']);
    });

    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
        Route::post('login-2fa', [\App\Http\Controllers\AuthController::class, 'login2FA']);
        Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::post('refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);
        Route::get('profile', [\App\Http\Controllers\AuthController::class, 'profile']);
        Route::post('request-forgot-password', [\App\Http\Controllers\AuthController::class, 'requestForgotPassword']);
        Route::get('reset-password', [\App\Http\Controllers\AuthController::class, 'forgotPasswordForm']);
        Route::put('reset-password', [\App\Http\Controllers\AuthController::class, 'resetPassword']);
        Route::put('change-password', [\App\Http\Controllers\AuthController::class, 'changePassword']);
        Route::post('check-auth', [\App\Http\Controllers\AuthController::class, 'checkAuth']);
    });

    Route::group([
        'prefix' => 'users'
    ], function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'getAll']);
        Route::post('/register', [App\Http\Controllers\UserController::class, 'signup']);
        Route::delete('/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
        Route::put('/profile', [App\Http\Controllers\UserController::class, 'updateProfile']);
        Route::put('/{id}', [App\Http\Controllers\UserController::class, 'updateUser']);


        Route::group([
            'prefix' => 'active'
        ], function () {
            Route::post('/send-mail', [App\Http\Controllers\UserController::class, 'activeByMail']);
            Route::put('/{hash}', [App\Http\Controllers\UserController::class, 'activeUsers']);
            Route::get('/{hash}', [App\Http\Controllers\UserController::class, 'viewActive']);
        });

        Route::group([
            'prefix' => 'setting'
        ], function () {
            Route::post('/enable-2fa', [App\Http\Controllers\UserController::class, '']);
            Route::put('/{hash}', [App\Http\Controllers\UserController::class, 'activeUsers']);
            Route::get('/{hash}', [App\Http\Controllers\UserController::class, 'viewActive']);
        });


        Route::group([
            'prefix' => 'manager'
        ], function () {
            Route::put('/{id}/role', [App\Http\Controllers\UserController::class, 'changeRole']);
            Route::put('/{id}/status', [App\Http\Controllers\UserController::class, 'changeStatus']);
        });
    });
    Route::post('/client/forgot-password', [ClientController::class, 'forgotPassword']);
    Route::post('/client/reset-password', [ClientController::class, 'resetPassword']);
    Route::get('/products/{id}/reviews', [App\Http\Controllers\ReviewController::class, 'getByProduct']);
    Route::prefix('reviews')->group(function () {
    Route::post('/', [App\Http\Controllers\ReviewController::class, 'store']);
    });
    Route::get('/reviews/{productId}', [App\Http\Controllers\ReviewController::class, 'getByProduct']);
    Route::get('/orders/{id}', [App\Http\Controllers\OrderController::class, 'getById']);
    Route::get('/orders/{id}/has-reviewed', [App\Http\Controllers\OrderController::class, 'checkReviewed']);

});
