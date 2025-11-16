<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomManagementController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrintReportController;
use App\Http\Controllers\RoomItemScanController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AdminMetricsController;
use App\Http\Controllers\ShareTokenController;

Route::get('/', function () {
    return view('welcome');
});

// CSRF token endpoint for refreshing tokens
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Test email system endpoint
Route::get('/test-email-system', [AuthController::class, 'testEmailSystem']);

// AUTHENTICATION ROUTES
Route::get('/register', [AuthController::class, 'showRegisterForm']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/verify-otp', [AuthController::class, 'verifyLoginOTP'])->name('login.verify-otp');
Route::post('/login/resend-otp', [AuthController::class, 'resendLoginOTP'])->name('login.resend-otp');
// Access token activation during signup
Route::post('/access-token/resend', [AuthController::class, 'resendAccessToken'])->name('access-token.resend');
Route::post('/access-token/verify', [AuthController::class, 'verifyAccessToken'])->name('access-token.verify');
// Device share token routes
Route::post('/device-share-token/generate', [AuthController::class, 'generateDeviceShareToken'])->middleware('auth')->name('device-share-token.generate');
Route::post('/device-share-token/verify', [AuthController::class, 'verifyDeviceShareToken'])->name('device-share-token.verify');
Route::get('/logout', [AuthController::class, 'logout']);
// Email verification during registration (OTP)
Route::post('/email-verification/send-otp', [AuthController::class, 'sendEmailVerificationOTP']);
Route::post('/email-verification/verify-otp', [AuthController::class, 'verifyEmailOTP']);
// Scan-based login helpers
Route::get('/login/id-image', [AuthController::class, 'idImage'])->name('login.id_image');
Route::post('/login/scan', [AuthController::class, 'loginByScan'])->name('login.scan');

// Password Reset Routes
Route::post('/password-reset/send-otp', [AuthController::class, 'sendOTP'])->name('password.reset.send-otp');
Route::post('/password-reset/verify-otp', [AuthController::class, 'verifyOTP'])->name('password.reset.verify-otp');
Route::post('/password-reset/update', [AuthController::class, 'updatePassword'])->name('password.reset.update');

// DASHBOARD (PROTECTED)
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth');
// ADMIN DASHBOARD VIEW (you can implement controller/view)
Route::get('/admin-dashboard', function(){ return view('admin-dashboard'); })->name('admin.dashboard');
Route::get('/admin-metrics', [AdminMetricsController::class, 'metrics'])->name('admin.metrics');

Route::middleware('auth')->group(function () {

    // USER APPROVAL
    Route::get('/add-new-user', [AuthController::class, 'showPendingAccounts']);
    Route::post('/approve-user/{id}', [AuthController::class, 'approveUser']);
    Route::post('/reject-user/{id}', [AuthController::class, 'rejectUser']);

    // ROOM MANAGEMENT
    Route::get('/manage-room', [RoomManagementController::class, 'index'])->name('room-manage');
    Route::post('/manage-room/item', [RoomManagementController::class, 'store'])->name('room-manage.store');
    Route::put('/manage-room/item/{item}', [RoomManagementController::class, 'update'])->name('room-manage.update');
    Route::delete('/manage-room/item/{item}', [RoomManagementController::class, 'destroy'])->name('room-manage.destroy');
    Route::delete('/manage-room/bulk-delete', [RoomManagementController::class, 'bulkDestroy'])->name('room-manage.bulk-destroy');
    Route::delete('/manage-room/room/{room}', [RoomManagementController::class, 'destroyRoom'])->name('room-manage.room-destroy');
    Route::post('/manage-room/pc/{room}/{pc}/component', [RoomManagementController::class, 'addComponent'])->name('room-manage.pc.add-component');
    
    
    // PHOTO DISPLAY ROUTES
    Route::get('/room-items/{id}/photo', [RoomManagementController::class, 'showPhoto'])->name('room-item.photo');
    
    // API endpoints for Room Management (used for AJAX calls)
    Route::get('/api/rooms', [RoomManagementController::class, 'getRoomsList']);
    Route::get('/api/full-set-components', [RoomManagementController::class, 'getFullSetComponents']);
    Route::get('/api/search-barcode/{pattern}', [RoomManagementController::class, 'searchByBarcode']);
    
    
    // FULL SET SPECIFIC ROUTES
    Route::get('/api/full-set/{fullSetId}/components', [RoomManagementController::class, 'getFullSetComponents'])->name('api.full-set.components');
    Route::post('/api/full-set/{fullSetId}/photo', [RoomManagementController::class, 'updateFullSetPhoto'])->name('api.full-set.photo');
    
    // SERIAL NUMBER GENERATION API
    Route::get('/api/next-serial-number', [RoomManagementController::class, 'getNextSerialNumber'])->name('api.next-serial-number');
    Route::get('/api/deleted-pc-numbers/{roomTitle}', [RoomManagementController::class, 'getDeletedPcNumbers'])->name('api.deleted-pc-numbers');
    Route::get('/room-manage/{id}/data', [RoomManagementController::class, 'getItemData'])->name('room-manage.data');

    // CATEGORY / DEVICE-TYPE MANAGEMENT
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/categories/items/{identifier}', [CategoryController::class, 'getItemsByIdentifier'])->name('categories.items');

    // ROOM ADDITION (FOR CATEGORY VIEW)
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');

    // MAINTENANCE
    Route::get('/maintenance', [MaintenanceController::class, 'index']);
    Route::post('/maintenance/update-status/{id}', [MaintenanceController::class, 'updateStatus']);
    Route::post('/maintenance/note/{fullsetId}', [MaintenanceController::class, 'updateNote'])->name('maintenance.update-note');
    Route::post('/maintenance/item-note/{id}', [MaintenanceController::class, 'updateItemNote'])->name('maintenance.item-note');
Route::post('/maintenance/bulk-update/{fullsetId}', [MaintenanceController::class, 'updateBulkStatus'])->name('maintenance.bulk-update');

    // BORROW ITEMS
    Route::get('/borrow', [BorrowController::class, 'index']);
    Route::post('/borrow', [BorrowController::class, 'store']);
    Route::post('/borrow/return/{id}', [BorrowController::class, 'returnItem']);
    Route::post('/borrow/return-bulk', [BorrowController::class, 'returnBulk'])->name('borrow.return-bulk');
    Route::post('/borrow/extend', [BorrowController::class, 'extend'])->name('borrow.extend');

    // PRINT REPORT
    Route::get('/print-report', [PrintReportController::class, 'index']);

    // SCAN BARCODE ROUTES
    Route::get('/scan-barcode', [RoomItemScanController::class, 'index'])->name('roomitem.scan.index');
    Route::post('/scan-barcode/search', [RoomItemScanController::class, 'search'])->name('roomitem.scan.search');
    // Add this line in your middleware('auth')->group(function () { section:
Route::post('/scan-barcode/api-search', [RoomItemScanController::class, 'apiSearch'])->name('roomitem.scan.api-search');

    // DEBUG ROUTE
    Route::get('/debug-items', function() {
        $items = App\Models\RoomItem::take(10)->get(['id', 'barcode', 'is_full_set_item', 'device_category', 'room_title']);
        return response()->json($items);
    });

    // SHARE TOKEN
    Route::get('/share/state', [ShareTokenController::class, 'state']);
    Route::post('/share/generate', [ShareTokenController::class, 'generate']);
    Route::post('/share/paste', [ShareTokenController::class, 'paste']);
    Route::post('/share/revoke/{sharedUser}', [ShareTokenController::class, 'revoke']);

    // DEVICE BINDING MANAGEMENT
    Route::post('/device-binding/remove/{id}', [AuthController::class, 'removeDeviceBinding'])->name('device-binding.remove');

    // PROFILE
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// SUPER ADMIN AUTH (backend endpoints expected)
Route::post('/super-admin/login', [SuperAdminController::class, 'login']);
Route::post('/super-admin/resend-otp', [SuperAdminController::class, 'resendOtp']);
Route::post('/super-admin/verify-otp', [SuperAdminController::class, 'verifyOtp']);
Route::get('/super-admin/status', [SuperAdminController::class, 'status']);
Route::post('/super-admin/register', [SuperAdminController::class, 'register']);