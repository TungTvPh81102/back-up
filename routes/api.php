<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Backend\AttributeController;
use App\Http\Controllers\API\Backend\AttributeValueController;
use App\Http\Controllers\API\Backend\BrandController;
use App\Http\Controllers\API\Backend\CategoryController;
use App\Http\Controllers\API\Backend\ProductController;
use App\Http\Controllers\API\Backend\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('v2')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/sign-up', [AuthController::class, 'signUp']);
        Route::post('/sign-in', [AuthController::class, 'signIn']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('v2')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/log-out', [AuthController::class, 'logout']);

            Route::get('user', function (Request $request) {
                return $request->user();
            });
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('v1')->group(function () {

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });

        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::get('/{category}', [CategoryController::class, 'show']);
            Route::put('/{category}', [CategoryController::class, 'update']);
            Route::delete('/{category}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('brands')->group(function () {
            Route::get('/', [BrandController::class, 'index']);
            Route::post('/', [BrandController::class, 'store']);
            Route::get('/{brand}', [BrandController::class, 'show']);
            Route::put('/{brand}', [BrandController::class, 'update']);
            Route::delete('/{brand}', [BrandController::class, 'destroy']);
        });

        Route::prefix('attributes')->group(function () {
            Route::get('/', [AttributeController::class, 'index']);
            Route::post('/', [AttributeController::class, 'store']);
            Route::get('/{attribute}', [AttributeController::class, 'show']);
            Route::put('/{attribute}', [AttributeController::class, 'update']);
            Route::delete('/{attribute}', [AttributeController::class, 'destroy']);
        });

        Route::prefix('attribute-values')->group(function () {
            Route::get('/', [AttributeValueController::class, 'index']);
            Route::post('/', [AttributeValueController::class, 'store']);
            Route::get('/{attributeValue}', [AttributeValueController::class, 'show']);
            Route::put('/{attributeValue}', [AttributeValueController::class, 'update']);
            Route::delete('/{attributeValue}', [AttributeValueController::class, 'destroy']);
        });

        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
        });
    });
});
