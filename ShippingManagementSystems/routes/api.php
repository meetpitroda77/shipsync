<?php


use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StripeSubscriptionWebhookController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::post('/stripe/webhook/subscription', [StripeSubscriptionWebhookController::class, 'handle']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail']);
Route::post('/track-shipment', [ShipmentController::class, 'trackShipmentApi']);
Route::get('/invoice/download/{shipment}', [ShipmentController::class, 'downloadInvoice'])
    ->name('api.invoice.download')
    ->middleware('signed');

Route::get('/payment/success/{shipmentId}', [ShipmentController::class, 'success'])
    ->name('payment.success');
Route::get('/payment/cancel/{shipmentId}', [ShipmentController::class, 'cancel'])
    ->name('payment.cancel');
Route::get('/subscription/success', [SubscriptionController::class, 'checkoutSuccess'] ?? function () {
    return redirect()->to(config('app.frontend_url') . '/subscription/success');
})->name('subscription.checkout.success');

Route::get('/subscription/cancel', function () {
    return redirect()->to(config('app.frontend_url') . '/subscription/cancel');
})->name('subscription.checkout.cancel');
Route::get('/subscription/status/check', [SubscriptionController::class, 'getSubscriptionStatus']);


Route::middleware('auth:sanctum')->prefix('subscription')->group(function () {
    Route::post('/checkout', [SubscriptionController::class, 'createCheckout']);
    Route::get('/dashboard', [SubscriptionController::class, 'dashboard']);
    Route::post('/cancel', [SubscriptionController::class, 'cancelSubscription']);
    Route::post('/reactivate', [SubscriptionController::class, 'reactivateSubscription']);
    Route::post('/upgrade-to-pro', [SubscriptionController::class, 'upgradeToPro']);
    Route::post('/billing-portal', [SubscriptionController::class, 'billingPortal']);
    Route::get('/can-create-shipment', [SubscriptionController::class, 'checkCanCreateShipment']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'editProfile']);
    Route::patch('/profile/update', [UserController::class, 'updateProfile']);

    Route::get('/shipments/{shipmentId}/payment-status', [ShipmentController::class, 'getPaymentStatus']);

    // Route::post('/track-shipment', [ShipmentController::class, 'trackShipmentApi']);

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/delete-all', [NotificationController::class, 'destroyAll']);
        Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{notificationId}', [NotificationController::class, 'destroy']);
    });

    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store'])->middleware('subscription.active');
    Route::post('/shipments/validate', [ShipmentController::class, 'validateShipment']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'getByID']);
    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update']);
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy']);
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'UpdateShipmentStatus']);

    Route::get('/payments', [ShipmentController::class, 'paymentShipments']);
    Route::post('/shipments/{shipment}/pay', [ShipmentController::class, 'payShipment']);
    Route::get('/shipments/{shipment}/invoice', [ShipmentController::class, 'generateInvoice']);

    Route::get('/recipients', [RecipientController::class, 'index']);
    Route::post('/recipients', [RecipientController::class, 'store']);
    Route::get('/recipients/{id}/edit', [RecipientController::class, 'edit']);
    Route::put('/recipients/{id}', [RecipientController::class, 'update']);
    Route::delete('/recipients/{recipient}', [RecipientController::class, 'destroy']);
    Route::get('/recipients/{id}/details', [RecipientController::class, 'getDetails']);
    Route::get('/recipients/all-with-addresses', [RecipientController::class, 'getAllWithAddresses']);


    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        $controller = new ShipmentController();

        switch ($user->role) {
            case 'admin':
                return $controller->adminDashboard();
            case 'staff':
                return $controller->StaffDashboard();
            case 'agent':
                return $controller->AgentDashboard($request);
            case 'customer':
                return $controller->customerDashboard($request);
            default:
                return response()->json(['success' => false, 'message' => 'Invalid role'], 403);
        }
    });
    Route::get('/settings', [SettingController::class, 'index']);

    Route::middleware('api.role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole']);

        Route::get('/reports', [ReportController::class, 'showReports']);
        Route::get('/report-general', [ShipmentController::class, 'report']);
        Route::get('/report-general/pdf', [ShipmentController::class, 'exportPdf']);
        Route::get('/report-general/excel', [ShipmentController::class, 'exportExcel']);
        Route::get('/report-general/csv', [ShipmentController::class, 'exportCsv']);

        Route::post('/settings', [SettingController::class, 'store']);
        Route::put('/settings/{setting}', [SettingController::class, 'update']);
        Route::delete('/settings/{setting}', [SettingController::class, 'destroy']);
    });
});
