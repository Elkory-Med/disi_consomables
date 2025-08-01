<?php

use App\Livewire\AboutUs;
use App\Livewire\Contacts;
use App\Livewire\AddCategory;
use App\Livewire\AllProducts;
use App\Livewire\EditProduct;
use App\Livewire\ManageOrders;
use App\Livewire\ManageProduct;
use App\Livewire\AddProductForm;
use App\Livewire\AdminDashboard;
use App\Livewire\ProductDetails;
use App\Livewire\ManageCategories;
use App\Livewire\UserOrderDetailsPage;
use App\Livewire\UserOrders;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\PendingUser;
use App\Livewire\ManageUsers;
use App\Livewire\UserDetailsPage;
use App\Livewire\OrdersDelivered;
use Illuminate\Support\Facades\Route;
use App\Livewire\ShoppingCartComponent;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ApprovedUser;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\PreventAdminAccess;
use App\Http\Middleware\RedirectAdminToAdminDashboard;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    // If user is admin, redirect to admin dashboard
    if (Auth::check() && Auth::user()->role == 1) {
        return redirect()->route('admin.dashboard');
    }
    
    return view('welcome');
})->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/auth/login', Login::class)->name('login');
    Route::get('/auth/register', Register::class)->name('register');
});

// Pending user route
Route::get('/auth/pending', PendingUser::class)->name('auth.pending');

// Public routes
Route::get('/about', AboutUs::class)->name('about');
Route::get('/contacts', Contacts::class)->name('contacts');
Route::get('/all/products', AllProducts::class)->name('products.all');
Route::get('/product/{product_id}/details', ProductDetails::class)->name('product.details');

// Protected routes that require approval
Route::middleware([Authenticate::class, ApprovedUser::class])->group(function () {
    Route::get('/my-orders', UserOrders::class)->name('user.orders');
    Route::get('/user-details/{userId}', UserOrderDetailsPage::class)->name('user.details');
});

Route::middleware([Authenticate::class, ApprovedUser::class, PreventAdminAccess::class])->group(function () {
    Route::get('/cart', ShoppingCartComponent::class)->name('cart');
});

// Basic auth routes (no approval needed)
Route::middleware([Authenticate::class])->group(function () {
    Route::get('/auth/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    })->name('auth.logout');
});

// Admin routes
Route::middleware([Authenticate::class, 'admin'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/products', ManageProduct::class)->name('admin.products');
    Route::get('/admin/orders', ManageOrders::class)->name('admin.orders');
    Route::get('/admin/categories', ManageCategories::class)->name('admin.categories');
    Route::get('/admin/categories/add', AddCategory::class)->name('admin.categories.add');
    Route::get('/admin/categories/manage', ManageCategories::class)->name('manage-categories');
    Route::get('/admin/products/add', AddProductForm::class)->name('admin.products.add');
    Route::get('/admin/products/{id}/edit', EditProduct::class)->name('admin.products.edit');
    Route::get('/admin/users', ManageUsers::class)->name('admin.users');
    Route::get('/invoice/{orderId}', [ManageOrders::class, 'generateInvoice'])->name('invoice.generate');
    Route::get('/orders-delivered', OrdersDelivered::class)->name('orders.delivered');
    Route::post('/generate-invoice/{orderId}', [OrdersDelivered::class, 'generateInvoice'])->name('generate.invoice');

    // Legacy dashboard data endpoint - still needed for compatibility
    Route::get('/admin/dashboard/data', [App\Http\Controllers\Admin\DashboardController::class, 'getDashboardData'])
        ->name('admin.dashboard.data');

    // Add specific endpoint for user deliveries chart
    Route::get('/admin/dashboard/data/user-deliveries', [App\Http\Controllers\Admin\DashboardController::class, 'getUserDeliveryData'])
        ->name('admin.dashboard.user-deliveries');
        
    // Cache clearing utility
    Route::get('/admin/clear-dashboard-cache', function() {
        // Clear all related caches
        \Cache::forget('dashboard_data_' . date('Y-m-d'));
        \Cache::forget('user_delivery_stats_' . now()->format('YmdH'));
        \Cache::forget('admin_dashboard_data');
        \Cache::forget('user_distribution_stats');
        \Cache::forget('delivered_products_stats');
        \Cache::forget('administration_stats');
        \Cache::forget('orders_trend_data');
        
        return redirect('/admin/dashboard?fullrefresh=' . time())
            ->with('message', 'Cache cleared successfully - loading fresh data');
    })->name('admin.dashboard.clear-cache');
});
