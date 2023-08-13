<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/





Route::group(['middleware' => ['guest']], function () {
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'authenticate'])->name('login.attempt');
});

Route::get('/orders/{order}/receipt', [\App\Http\Controllers\OrderController::class, 'receipt'])->name('orders.receipt');
Route::get('/qr-code/{value}/test', [\App\Http\Controllers\QrCodeController::class, 'show'])->name('qr-code.show');


Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'show'])->name('home');
    Route::view('/about', 'about.show')->name('about');
    Route::get('/point-of-sale', [\App\Http\Controllers\PointOfSaleController::class, 'show'])->name('pos.show');
    Route::post('logout', [\App\Http\Controllers\Auth\LogOutController::class, 'logout'])->name('logout');


    Route::get('password/confirm', [\App\Http\Controllers\Auth\ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [\App\Http\Controllers\Auth\ConfirmPasswordController::class, 'confirm']);

    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/create', [\App\Http\Controllers\CategoryController::class, 'create'])->name('categories.create');
    Route::get('/categories/{category}/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::delete('/categories/{category}/image', [\App\Http\Controllers\CategoryController::class, 'imageDestroy'])->name('categories.image.destroy');


    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/create', [\App\Http\Controllers\ProductController::class, 'create'])->name('products.create');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{product}/image', [\App\Http\Controllers\ProductController::class, 'imageDestroy'])->name('products.image.destroy');


    Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/create', [\App\Http\Controllers\CustomerController::class, 'create'])->name('customers.create');
    Route::get('/customers/{customer}/edit', [\App\Http\Controllers\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->name('customers.destroy');



    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/analytics', [\App\Http\Controllers\OrderController::class, 'showAnalytics'])->name('orders.analytics');

    Route::get('/orders/filter', [\App\Http\Controllers\OrderController::class, 'filter'])->name('orders.filter');
    Route::get('/orders/print/{order}', [\App\Http\Controllers\OrderController::class, 'print'])->name('orders.print');
    Route::get('/orders/edit/{order}', [\App\Http\Controllers\OrderController::class, 'edit'])->name('orders.edit');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'destroy'])->name('orders.destroy');


    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'show'])->name('settings.show');
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');


    Route::get('/change-password', [\App\Http\Controllers\PasswordController::class, 'show'])->name('password.show');
    Route::put('/change-password', [\App\Http\Controllers\PasswordController::class, 'update'])->name('password.update');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');


    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    //API
    Route::get('/inventory/categories', [\App\Http\Controllers\InventoryController::class, 'getCategories']);
    Route::get('/inventory/products', [\App\Http\Controllers\InventoryController::class, 'getProducts']);
    Route::get('/customers/search/{name}', [\App\Http\Controllers\CustomerController::class, 'searchByName']);
    Route::post('/customers/create-new', [\App\Http\Controllers\CustomerController::class, 'createNew']);


    Route::post('/order', [\App\Http\Controllers\OrderController::class, 'store']);


    Route::get('/uploads/{path}', [App\Http\Controllers\ImageController::class, 'show'])->where('path', '.*');
});
