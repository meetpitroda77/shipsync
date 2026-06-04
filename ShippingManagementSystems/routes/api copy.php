<?php

use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\UserController;
use App\Models\Recipient;
use Illuminate\Support\Facades\Route;   
use Illuminate\Http\Request;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail']);

Route::get('/track-shipment', [ShipmentController::class, 'trackShipmentApi']);

Route::get('/invoice/download/{shipment}', [ShipmentController::class, 'downloadInvoice'])
    ->name('api.invoice.download')
    ->middleware('signed');

Route::get('/payment/success', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Payment successful',
        'session_id' => $request->session_id,
        'shipment_id' => $request->shipment_id
    ]);
})->name('api.payment.success.callback');

Route::get('/payment/cancel', function (Request $request) {
    return response()->json([
        'success' => false,
        'message' => 'Payment cancelled',
        'shipment_id' => $request->shipment_id
    ]);
})->name('api.payment.cancel.callback');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/delete-all', [NotificationController::class, 'destroyAll']);
        Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{notificationId}', [NotificationController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum', 'api.role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [ShipmentController::class, 'adminDashboard']);

    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::post('/shipments/validate', [ShipmentController::class, 'validateShipment']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'getByID']);
    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update']);
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy']);
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'UpdateShipmentStatus']);

    Route::get('/payments', [ShipmentController::class, 'paymentShipments']);
    Route::get('/shipments/{shipment}/success', [ShipmentController::class, 'success']);
    Route::get('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel']);
    Route::post('/shipments/{shipment}/pay', [ShipmentController::class, 'payShipment']);
    Route::get('/shipments/{shipment}/invoice', [ShipmentController::class, 'generateInvoice']);

    Route::get('/reports', [ReportController::class, 'showReports']);
    Route::get('/report-general', [ShipmentController::class, 'report']);
    Route::get('/report-general/pdf', [ShipmentController::class, 'exportPdf']);
    Route::get('/report-general/excel', [ShipmentController::class, 'exportExcel']);
    Route::get('/report-general/csv', [ShipmentController::class, 'exportCsv']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}/api.role', [UserController::class, 'updateapi.role']);
    Route::get('/profile/{user}/edit', [UserController::class, 'editProfile']);
    Route::patch('/profile/update', [UserController::class, 'updateProfile']);

    Route::get('/recipients', [RecipientController::class, 'index']);
    Route::get('/recipients/create', [RecipientController::class, 'recipientform']);
    Route::post('/recipients', [RecipientController::class, 'store']);
    Route::get('/recipients/{id}/edit', [RecipientController::class, 'edit']);
    Route::put('/recipients/{id}', [RecipientController::class, 'update']);
    Route::delete('/recipients/{recipient}', [RecipientController::class, 'destroy']);

    Route::get('/recipients/{id}/details', function ($id) {
        $recipient = Recipient::with('addresses')->find($id);
        if ($recipient) {
            return response()->json([
                'success' => true,
                'data' => [
                    'phone' => $recipient->receiver_phone,
                    'addresses' => $recipient->addresses,
                ]
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Recipient not found'], 404);
    });

    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'store']);
    Route::put('/settings/{setting}', [SettingController::class, 'update']);
    Route::delete('/settings/{setting}', [SettingController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'api.role:customer'])->prefix('customer')->group(function () {
    Route::get('/dashboard', [ShipmentController::class, 'customerDashboard']);

    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::post('/shipments/validate', [ShipmentController::class, 'validateShipment']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'getByID']);
    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update']);
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy']);

    Route::get('/payments', [ShipmentController::class, 'paymentShipments']);
    Route::get('/shipments/{shipment}/success', [ShipmentController::class, 'success']);
    Route::get('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel']);
    Route::post('/shipments/{shipment}/pay', [ShipmentController::class, 'payShipment']);
    Route::get('/shipments/{shipment}/invoice', [ShipmentController::class, 'generateInvoice']);

    Route::get('/profile/{user}/edit', [UserController::class, 'editProfile']);
    Route::patch('/profile/update', [UserController::class, 'updateProfile']);

    Route::get('/recipients', [RecipientController::class, 'index']);
    Route::get('/recipients/create', [RecipientController::class, 'recipientform']);
    Route::post('/recipients', [RecipientController::class, 'store']);
    Route::get('/recipients/{id}/edit', [RecipientController::class, 'edit']);
    Route::put('/recipients/{id}', [RecipientController::class, 'update']);
    Route::delete('/recipients/{recipient}', [RecipientController::class, 'destroy']);

    Route::get('/recipients/{id}/details', function ($id) {
        $recipient = Recipient::with('addresses')->find($id);
        if ($recipient) {
            return response()->json([
                'success' => true,
                'data' => [
                    'phone' => $recipient->receiver_phone,
                    'addresses' => $recipient->addresses,
                ]
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Recipient not found'], 404);
    })->name('api.customer.recipient.details');

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'api.role:staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [ShipmentController::class, 'StaffDashboard']);
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'getByID']);
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'UpdateShipmentStatus']);
    Route::get('/payments', [ShipmentController::class, 'paymentShipments']);
    Route::get('/shipments/{shipment}/invoice', [ShipmentController::class, 'generateInvoice']);
    Route::get('/profile/{user}/edit', [UserController::class, 'editProfile']);
    Route::patch('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'api.role:agent'])->prefix('agent')->group(function () {
    Route::get('/dashboard', [ShipmentController::class, 'AgentDashboard']);
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'getByID']);
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'UpdateShipmentStatus']);
    Route::get('/payments', [ShipmentController::class, 'paymentShipments']);
    Route::get('/shipments/{shipment}/invoice', [ShipmentController::class, 'generateInvoice']);
    Route::get('/profile/{user}/edit', [UserController::class, 'editProfile']);
    Route::patch('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'api.role:admin,staff'])->prefix('reports')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'message' => 'Reports endpoint'
        ]);
    });
});

Route::post('/logout', [AuthController::class, 'logout']);
