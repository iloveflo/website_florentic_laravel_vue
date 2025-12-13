<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Product\ProductDetailsController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\OrderUserController;
use App\Http\Controllers\Admin\CouponController;

Route::get('/captcha', [AuthController::class, 'getCaptcha']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

// các route liên quan đến admin
Route::middleware('auth:sanctum', 'admin')->prefix('admin')->group(function () {
    // quản lý đơn hàng
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order_code}', [OrderController::class, 'show']);
    Route::put('/{order_code}/status', [OrderController::class, 'updateStatus']);

    //quản lý người dùng
    Route::get('/users/deleted', [UserController::class, 'deletedUsers']);       // danh sách đã xóa
    Route::patch('/users/{id}/restore', [UserController::class, 'restore']);    // khôi phục
    Route::get('/users', [UserController::class, 'index']);         // danh sách
    Route::post('/users', [UserController::class, 'store']);        // tạo user/admin
    Route::get('/users/{id}', [UserController::class, 'show']);      // xem chi tiết
    Route::put('/users/{id}', [UserController::class, 'update']);    // update
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // xóa user
    Route::patch('/users/{id}/status', [UserController::class, 'changeStatus']); // change status
    Route::get('/users/{id}/orders', [UserController::class, 'orders']); // lịch sử đơn hàng

    //thống kê báo cáo 
    Route::get('/statistics/overview', [StatisticsController::class, 'getOverview']);    // Dành cho KPI cards và các số liệu tổng quan
    Route::get('/statistics/revenue-over-time', [StatisticsController::class, 'getRevenueOverTime']); // Dành cho biểu đồ doanh thu
    Route::get('/statistics/sales-by-category', [StatisticsController::class, 'getSalesByCategory']); // Dành cho biểu đồ sản phẩm bán theo danh mục
    Route::get('/statistics/order-status-distribution', [StatisticsController::class, 'getOrderStatusDistribution']); // Dành cho biểu đồ tròn trạng thái đơn hàng
    Route::get('/statistics/top-selling-products', [StatisticsController::class, 'getTopSellingProducts']); // Dành cho top selling
    Route::get('/statistics/recent-activities', [StatisticsController::class, 'getRecentActivities']); // Lấy danh sách đơn hàng mới, sản phẩm sắp hết hàng
    Route::get('/statistics/export', [StatisticsController::class, 'exportReport']);   // Export dữ liệu

    // quản lý sản phẩm
    Route::get('/products/categories', [CategoryController::class, 'index']);
    Route::get('/products/categories/tree', [CategoryController::class, 'tree']);
    Route::get('/products', [ProductAdminController::class, 'index']);
    Route::post('/products', [ProductAdminController::class, 'store']);
    Route::get('/products/{id}', [ProductAdminController::class, 'show']);
    Route::put('/products/{id}', [ProductAdminController::class, 'update']);
    Route::delete('/products/{id}', [ProductAdminController::class, 'destroy']);
    Route::get('/products/{product}/images', [ProductImageController::class, 'index']);
    Route::post('/products/{product}/images', [ProductImageController::class, 'store']);
    Route::delete('/products/images/{image}', [ProductImageController::class, 'destroy']);
    Route::patch('/products/images/{image}/primary', [ProductImageController::class, 'setPrimary']);

    //quản lý khuyến mại
    Route::apiResource('coupons', CouponController::class);
});

//hiển thị sản phẩm
Route::get('/products/category/{slug}', [ProductController::class, 'getByCategory']);
Route::get('/products', [ProductController::class, 'getAll']);
Route::get('/products/{slug}', [ProductDetailsController::class, 'show']);//chi tiết sản phẩm

// tìm kiếm
Route::get('/search', [SearchController::class, 'getAll']);

//tài khoản khách hàng
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/update', [ProfileController::class, 'update']);
});

//hiển thị đơn hàng
Route::get('/orders', [OrderUserController::class, 'index']);
Route::get('/orders/{code}', [OrderUserController::class, 'show']);

// route giỏ hàng
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addToCart']);
    Route::put('/update', [CartController::class, 'update']);
    Route::delete('/remove/{id}', [CartController::class, 'remove']);
    Route::post('/buy-again', [CartController::class, 'buyAgain']);
});

//route đặt hàng
Route::get('/checkout/info', [CheckoutController::class, 'getCheckoutInfo']);
Route::post('/checkout/process', [CheckoutController::class, 'processCheckout']);
Route::get('/payment/vnpay-callback', [CheckoutController::class, 'vnpayCallback']);

//đánh giá
Route::get('/orders/{order_code}/review-info', [OrderController::class, 'getReviewInfo']);
Route::post('/reviews', [OrderController::class, 'storeReviews']);

//AI
Route::post('/chat', [ChatController::class, 'sendMessage']);
Route::get('/chat/history', [ChatController::class, 'getHistory']);