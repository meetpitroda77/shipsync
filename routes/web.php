<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\UserController;
use App\Models\Recipient;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('pages.herosection');
});

Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login');

Route::get('/register', function () {
    return view('pages.auth.register');
})->name('register');

Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');



Route::get('/forgot-password', function () {
    return view('pages.auth.forgotPassword');
})->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('pages.auth.resetPassword', ['token' => $token]);
})->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');



Route::get('/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
    ->name('shipment.invoice');
Route::get('/track-shipment', [ShipmentController::class, 'showTrackForm'])->name('shipment.track.form');
Route::get('/track-shipment-result', [ShipmentController::class, 'trackShipment'])->name('shipment.track');
Route::middleware('auth')->get('/notifications/unread-count', function () {
    return response()->json([
        'count' => auth()->user()->unreadNotifications()->count()
    ]);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [ShipmentController::class, 'adminDashboard'])->name('admin');

    Route::get('/admin/createShipment', function () {

        $sender = Auth::user()->load([
            'addresses' => function ($query) {
                $query->whereNull('recipient_id');
            }
        ]);
        $recipients = Recipient::all()->load('addresses');
        return view('pages.shipment.createShipment', compact('sender', 'recipients'));
    })->name('admin.createShipment');
    Route::post('/admin/createShipment', [ShipmentController::class, 'store'])
        ->name('admin.storeShipment');

    Route::get('/admin/get-recipient-details/{id}', function ($id) {
        $recipient = Recipient::with('addresses')->find($id);

        if ($recipient) {
            return response()->json([
                'phone' => $recipient->receiver_phone,
                'addresses' => $recipient->addresses,
            ]);
        }

        return response()->json(['message' => 'Recipient not found'], 404);
    });

    Route::get('/admin/shipments', [ShipmentController::class, 'index'])
        ->name('admin.shipments');

    Route::get('/admin/shipments/{shipment}', [ShipmentController::class, 'getByID'])
        ->name('admin.shipments.show');

    Route::get('/admin/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])
        ->name('admin.editShipment');

    Route::put('/admin/shipments/{shipment}', [ShipmentController::class, 'update'])
        ->name('admin.updateShipment');
    Route::delete('/admin/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('admin.destroyShipment');


    Route::delete('/admin/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('admin.destroyShipment');
    Route::get('/admin/shipment/success/{shipment}', [ShipmentController::class, 'success'])
        ->name('admin.shipments.success');

    Route::get('/admin/shipment/cancel/{shipment}', [ShipmentController::class, 'cancel'])
        ->name('admin.shipments.cancel');
    Route::post('/admin/shipments/{shipment}/pay', [ShipmentController::class, 'payShipment'])
        ->name('admin.payShipment');

    Route::get('/admin/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
        ->name('admin.shipment.invoice');
    Route::get('/admin/track-shipment', [ShipmentController::class, 'showTrackForm'])->name('admin.shipment.track.form');
    Route::get('/admin/track-shipment-result', [ShipmentController::class, 'trackShipment'])->name('admin.shipment.track');
    Route::patch('/admin/shipment/{shipment}/UpdateShipmentStatus', [ShipmentController::class, 'UpdateShipmentStatus'])
        ->name('admin.shipment.UpdateStatus');
    Route::get('/admin/shipment/paymentShipments', [ShipmentController::class, 'paymentShipments'])->name('admin.shipment.paymentShipments');
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');

    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])
        ->name('admin.destroyUser');
    Route::post('/admin/users/store', [UserController::class, 'store'])
        ->name('admin.users.store');

    Route::post('/admin/users/store', [UserController::class, 'store'])
        ->name('admin.users.store');
    Route::patch('/admin/users/updateRole', [UserController::class, 'updateRole'])
        ->name('admin.user.update.role');


    Route::get('/admin/recipient', [RecipientController::class, 'index'])->name('admin.recipient.index');

    Route::get('/admin/recipient/create', [RecipientController::class, 'recipientform'])->name('admin.recipient.recipientform');
    Route::post('/admin/recipient/store', [RecipientController::class, 'store'])->name('admin.recipient.store');
    Route::delete('/admin/recipient/destroy/{recipient}', [RecipientController::class, 'destroy'])->name('admin.recipient.destroy');

    Route::get('/admin/recipient/{id}/edit', [RecipientController::class, 'edit'])->name('admin.recipient.edit');

    Route::put('/admin/recipient/{id}', [RecipientController::class, 'update'])->name('admin.recipient.update');


    Route::get('/admin/profile/edit/{user}', [UserController::class, 'editProfile'])->name('admin.profile.editProfile');
    Route::patch('/admin/profile/update', [UserController::class, 'updateProfile'])->name('admin.profile.updateProfile');


    Route::post('/admin/notifications/{notificationId}/read', function ($notificationId) {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['status' => 'success']);
    });

    Route::get('/admin/reports', [ReportController::class, 'showReports'])->name('admin.reports');


    Route::get('/admin/reportGeneral', [ShipmentController::class, 'report'])->name('reportgeneral');
    Route::get('/admin/reportGeneral/pdf', [ShipmentController::class, 'exportPdf'])->name('report.pdf');
    Route::get('/admin/reportGeneral/excel', [ShipmentController::class, 'exportExcel'])->name('report.excel');
    Route::get('/admin/reportGeneral/csv', [ShipmentController::class, 'exportCsv'])->name('report.csv');

    Route::get('/admin/settings', [SettingController::class, 'index'])->name('getsetting');
    Route::post('/admin/settings', [SettingController::class, 'store'])->name('createsetting');
    Route::put('/admin/settings/{setting}', [SettingController::class, 'update'])->name('updatesetting');
    Route::delete('/admin/settings/{setting}', [SettingController::class, 'destroy'])->name('destroySetting');

});



Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/customer', [ShipmentController::class, 'customerDashboard'])->name('customer');

    Route::get('/customer/createShipment', function () {

        $sender = Auth::user()->load([
            'addresses' => function ($query) {
                $query->whereNull('recipient_id');
            }
        ]);

        $recipients =
            Recipient::with('addresses')
                ->where('user_id', Auth::id())
                ->get();

        return view(
            'pages.shipment.createShipment',
            compact('sender', 'recipients')
        );

    })->name('customer.createShipment');
    Route::post('/customer/createShipment', [ShipmentController::class, 'store'])
        ->name('customer.storeShipment');

    Route::get('/customer/shipments', [ShipmentController::class, 'index'])
        ->name('customer.shipments');

    Route::get('/customer/shipments/{shipment}', [ShipmentController::class, 'getByID'])
        ->name('customer.shipments.show');



    Route::get('/customer/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])
        ->name('customer.editShipment');

    Route::put('/customer/shipments/{shipment}', [ShipmentController::class, 'update'])
        ->name('customer.updateShipment');

    Route::delete('/customer/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('customer.destroyShipment');
    Route::get('/customer/shipment/success/{shipment}', [ShipmentController::class, 'success'])
        ->name('customer.shipments.success');

    Route::get('/customer/shipment/cancel/{shipment}', [ShipmentController::class, 'cancel'])
        ->name('customer.shipments.cancel');
    Route::post('/customer/shipments/{shipment}/pay', [ShipmentController::class, 'payShipment'])
        ->name('customer.payShipment');

    Route::get('/customer/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
        ->name('customer.shipment.invoice');
    Route::get('/customer/track-shipment', [ShipmentController::class, 'showTrackForm'])->name('customer.shipment.track.form');
    Route::get('/customer/track-shipment-result', [ShipmentController::class, 'trackShipment'])->name('customer.shipment.track');

    Route::get('/customer/shipment/paymentShipments', [ShipmentController::class, 'paymentShipments'])->name('customer.shipment.paymentShipments');
    Route::get('/customer/recipient', [RecipientController::class, 'index'])->name('customer.recipient.index');
    Route::get('/customer/recipient/create', [RecipientController::class, 'recipientform'])->name('customer.recipient.recipientform');
    Route::post('/customer/recipient/store', [RecipientController::class, 'store'])->name('customer.recipient.store');
    Route::delete('/customer/recipient/destroy/{recipient}', [RecipientController::class, 'destroy'])->name('customer.recipient.destroy');

    Route::get('/customer/recipient/{id}/edit', [RecipientController::class, 'edit'])->name('customer.recipient.edit');

    Route::put('/customer/recipient/{id}', [RecipientController::class, 'update'])->name('customer.recipient.update');

    Route::get('/customer/get-recipient-details/{id}', function ($id) {
        $recipient = Recipient::with('addresses')->find($id);

        if ($recipient) {
            return response()->json([
                'phone' => $recipient->receiver_phone,
                'addresses' => $recipient->addresses,
            ]);
        }

        return response()->json(['message' => 'Recipient not found'], 404);
    })->name('getRecipientDetails');
    Route::post('/customer/logout', [AuthController::class, 'logout'])->name('customer.logout');


    Route::get('/customer/profile/edit/{user}', [UserController::class, 'editProfile'])->name('customer.profile.editProfile');
    Route::patch('/customer/profile/update', [UserController::class, 'updateProfile'])->name('customer.profile.updateProfile');


});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff', [ShipmentController::class, 'StaffDashboard'])->name('staff');

    Route::get('/staff/shipments', [ShipmentController::class, 'index'])
        ->name('staff.shipments');

    Route::get('/staff/shipments/{shipment}', [ShipmentController::class, 'getByID'])
        ->name('staff.shipments.show');

    Route::get('/staff/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
        ->name('staff.shipment.invoice');
    Route::patch('/staff/shipment/{shipment}/UpdateShipmentStatus', [ShipmentController::class, 'UpdateShipmentStatus'])
        ->name('staff.shipment.UpdateStatus');


    Route::get('/staff/shipment/paymentShipments', [ShipmentController::class, 'paymentShipments'])->name('staff.shipment.paymentShipments');



    Route::get('/staff/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
        ->name('staff.shipment.invoice');
    Route::get('/staff/track-shipment', [ShipmentController::class, 'showTrackForm'])->name('staff.shipment.track.form');
    Route::get('/staff/track-shipment-result', [ShipmentController::class, 'trackShipment'])->name('staff.shipment.track');

    Route::get('/staff/profile/edit/{user}', [UserController::class, 'editProfile'])->name('staff.profile.editProfile');
    Route::patch('/staff/profile/update', [UserController::class, 'updateProfile'])->name('staff.profile.updateProfile');


    Route::post('/staff/notifications/{notificationId}/read', function ($notificationId) {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['status' => 'success']);
    });


    Route::post('/staff/logout', [AuthController::class, 'logout'])->name('staff.logout');






});



Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/agent', [ShipmentController::class, 'AgentDashboard'])->name('agent');


    Route::get('/agent/shipments', [ShipmentController::class, 'index'])
        ->name('agent.shipments');

    Route::get('/agent/shipments/{shipment}', [ShipmentController::class, 'getByID'])
        ->name('agent.shipments.show');
    Route::get('/agent/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
        ->name('agent.shipment.invoice');
    Route::patch('/agent/shipment/{shipment}/UpdateShipmentStatus', [ShipmentController::class, 'UpdateShipmentStatus'])
        ->name('agent.shipment.UpdateStatus');
    Route::get('/agent/shipment/paymentShipments', [ShipmentController::class, 'paymentShipments'])->name('agent.shipment.paymentShipments');
    Route::post('/agent/logout', [AuthController::class, 'logout'])->name('agent.logout');

    Route::get('/agent/profile/edit/{user}', [UserController::class, 'editProfile'])->name('agent.profile.editProfile');
    Route::patch('/agent/profile/update', [UserController::class, 'updateProfile'])->name('agent.profile.updateProfile');

    Route::get('/agent/shipment/{shipment}/invoice', [ShipmentController::class, 'generateInvoice'])
        ->name('agent.shipment.invoice');
    Route::get('/agent/track-shipment', [ShipmentController::class, 'showTrackForm'])->name('agent.shipment.track.form');
    Route::get('/agent/track-shipment-result', [ShipmentController::class, 'trackShipment'])->name('agent.shipment.track');


    Route::post('/agent/notifications/{notificationId}/read', function ($notificationId) {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['status' => 'success']);
    });
});

Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/reports', function () {
        return view('pages.reports');
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
