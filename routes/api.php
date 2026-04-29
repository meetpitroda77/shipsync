<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Recipient;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);


Route::post('/login', [AuthController::class, 'login']);

Route::get('/track-shipment', [ShipmentController::class, 'trackShipmentApi']);



Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {

    Route::post('/customer/createShipment', [ShipmentController::class, 'store']);
    Route::post('/customer/validateShipment', [ShipmentController::class, 'validateShipment']);


});




Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::post('/admin/createShipment', [ShipmentController::class, 'store']);
    Route::post('/admin/validateShipment',[ShipmentController::class,'validateShipment']);

    Route::patch('/admin/shipment/{shipment}/UpdateShipmentStatus', [ShipmentController::class, 'UpdateShipmentStatus']);

});



Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {

    Route::patch('/staff/shipment/{shipment}/UpdateShipmentStatus', [ShipmentController::class, 'UpdateShipmentStatus']);

});

Route::middleware(['auth:sanctum', 'role:agent'])->group(function () {

    Route::patch('/agent/shipment/{shipment}/UpdateShipmentStatus', [ShipmentController::class, 'UpdateShipmentStatus']);

});






