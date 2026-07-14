<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\StripeController;
use App\Http\Controllers\Admin\SettingController;

use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\NotificationController;

use App\Http\Controllers\PublicProductController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('product/{product}', [PublicProductController::class, 'show'])->name('public.product.show');

// Guest Checkout & Payment Simulation Endpoints
Route::get('orders/{order}/mock-pay', [OrderController::class, 'mockPay'])->name('orders.mock-pay');
Route::post('orders/{order}/mock-pay/submit', [OrderController::class, 'mockPaySubmit'])->name('orders.mock-pay.submit');
Route::get('orders/{order}/stripe-success', [OrderController::class, 'stripeSuccess'])->name('orders.stripe-success');
Route::get('orders/{order}/thank-you', function (App\Models\Order $order) {
    return view('front.thankyou', compact('order'));
})->name('orders.thank-you');

Route::get('/store-selector', [\App\Http\Controllers\StoreSelectorController::class, 'index'])->name('store.selector');

Route::get('/shop/{uuid}', [\App\Http\Controllers\ShopController::class, 'index'])->name('shop.index');
Route::post('/shop/{uuid}/order', [\App\Http\Controllers\ShopController::class, 'placeOrder'])->name('shop.place-order');
Route::get('/shop/{uuid}/search', [\App\Http\Controllers\ShopController::class, 'search'])->name('shop.search');

Route::prefix('admin')->group(function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Protected Routes
    Route::middleware('auth')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

        // Advanced Analytics Reporting
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports');
        Route::get('reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('admin.reports.export');

        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class);
        Route::delete('orders/{order}/item/{item}', [OrderController::class, 'destroyItem'])->name('orders.items.destroy');
        Route::get('orders/{order}/export-pdf', [OrderController::class, 'exportPdf'])->name('orders.export-pdf');
        Route::resource('stores', StoreController::class);
        Route::post('orders/{order}/send-payment-link', [OrderController::class, 'sendPaymentLink'])->name('orders.send-payment-link');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');

        // Stripe integration settings & status page
        Route::get('stripe', [StripeController::class, 'index'])->name('admin.stripe');

        // Global Settings
        Route::get('settings', [SettingController::class, 'index'])->name('admin.settings');
        Route::post('settings', [SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('/products-sync-whatsapp', function () {
            set_time_limit(0); // Prevent timeout on shared hosting
            \Illuminate\Support\Facades\Artisan::call('whatsapp:sync-catalog');
            return response()->json([
                'message' => 'Sync command executed.',
                'output' => \Illuminate\Support\Facades\Artisan::output()
            ]);
        })->name('products.sync-whatsapp');

        Route::get('/clear-cache', function () {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            return 'Cache cleared successfully!';
        })->name('admin.clear-cache');
    });
});
