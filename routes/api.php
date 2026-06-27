<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PayController;


Route::post('/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/products', [AuthController::class, 'showProducts']); //categary name and id
Route::get('/admin-work', [AuthController::class, 'showAdminWork']); // Public route to view slides

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/add-admin', [AuthController::class, 'addAdmin'])->name('add-admin');
    Route::post('/add-product', [AuthController::class, 'addProduct'])->name('add-product');
    Route::get('/product-details/{id}', [AuthController::class, 'productDetails']);
    Route::get('/product-size-quantity', [AuthController::class, 'getProductSizeAndQuantity']);
    Route::get('/related-products/{id}', [AuthController::class, 'relatedProducts']);
    Route::post('/add-slide', [AuthController::class, 'addSlide'])->name('add-slide'); // Route for adding slides
    Route::get('/showAdminWork', [AuthController::class, 'showAdminWork'])->name('showAdminWork');
    Route::post('/admin-work/{adminWorkId}', [AuthController::class, 'updateAdminWorkWithProducts']);
    Route::get('/users', [AuthController::class, 'showAllUsers'])->name('showAllUsers');
    Route::post('/add-order', [AuthController::class, 'addOrder'])->name('add-order');
    Route::get('/orders/{id}', [AuthController::class, 'showOrder'])->name('showOrder');
    Route::get('/orders', [AuthController::class, 'showOrderAdmin'])->name('showOrderAdmin');
    Route::post('/billing-data', [AuthController::class, 'storeBillingData']);
    Route::post('/create-payment/{orderId}', [PayController::class, 'createPay']);
    Route::post('/admin/add-code', [AuthController::class, 'addCode']);
    Route::post('/check-code', [AuthController::class, 'checkCode']);
    Route::post('/admin/add-coupon', [AuthController::class, 'addCoupon']);
    Route::post('/check-coupon', [AuthController::class, 'checkCoupon']);
    Route::get('/admin/show-codes', [AuthController::class, 'showCodes']);
    Route::get('/admin/show-coupons', [AuthController::class, 'showCoupons']);
    Route::post('/products/{id}', [AuthController::class, 'deleteProduct']);
    Route::get('/users/{userId}/last-order', [AuthController::class, 'getLastOrderWithItems']);



});

