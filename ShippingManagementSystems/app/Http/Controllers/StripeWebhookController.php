<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PaymentStatusNotification;
use App\Notifications\ShipmentStatusNotification;
use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\Payment;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    private function sendPaymentNotification($shipment, $payment, $status, $reason = null)
    {
        $user = User::find($shipment->created_by);

        if ($user) {
            $user->notify(new PaymentStatusNotification(
                $shipment,
                $payment,
                $status,
                $reason
            ));
        }
    }
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':

                $session = $event->data->object;

                Payment::where('session_id', $session->id)->update([
                    'transaction_id' => $session->payment_intent,
                ]);

                break;

            case 'payment_intent.processing':

                $paymentIntent = $event->data->object;

                $shipmentId = $paymentIntent->metadata->shipment_id ?? null;


                if (!$shipmentId) {
                    break;
                }

                $payment = Payment::where('shipment_id', $shipmentId)
                    ->latest()
                    ->first();

                if (!$payment) {
                    break;
                }

                if ($payment->payment_status === 'paid') {
                    break;
                }

                $payment->update([
                    'transaction_id' => $paymentIntent->id,
                    'payment_status' => 'processing',
                ]);

                $shipment = Shipment::find($payment->shipment_id);

                if ($shipment && $shipment->status !== 'processing_payment') {
                    $shipment->update(['status' => 'processing_payment']);

                    $shipment->logs()->create([
                        'status' => 'processing_payment',
                        'description' => 'Payment is processing via Stripe'
                    ]);
                }

                $creator = User::find($shipment->created_by);

                $this->sendPaymentNotification($shipment, $payment, 'PROCESSING');
                break;

            case 'payment_intent.succeeded':

                $paymentIntent = $event->data->object;

                $shipmentId = $paymentIntent->metadata->shipment_id ?? null;

                $shipmentId = $paymentIntent->metadata->shipment_id ?? null;

                if (!$shipmentId) {
                    break;
                }

                $payment = Payment::where('shipment_id', $shipmentId)
                    ->latest()
                    ->first();

                if (!$payment) {
                    break;
                }

                if ($payment->payment_status === 'paid')
                    break;

                $payment->update([
                    'transaction_id' => $paymentIntent->id,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                $shipment = Shipment::find($payment->shipment_id);

                if ($shipment) {
                    $shipment->update(['status' => 'pending_assigned']);

                    $shipment->logs()->create([
                        'status' => 'pending_assigned',
                        'description' => 'Payment confirmed via Stripe'
                    ]);

                    $creator = User::find($shipment->created_by);
                    if ($creator) {
                        $creator->notify(new ShipmentStatusNotification($shipment));
                    }

                    $this->sendPaymentNotification($shipment, $payment, 'SUCCESS');
                }

                break;

            case 'payment_intent.payment_failed':
            case 'charge.failed':

                $paymentIntent = $event->data->object;

                $shipmentId = $paymentIntent->metadata->shipment_id ?? null;


                if (!$shipmentId) {
                    break;
                }

                $payment = Payment::where('shipment_id', $shipmentId)
                    ->latest()
                    ->first();

                if (!$payment) {
                    break;
                }
                if ($payment->payment_status === 'paid') {
                    break;
                }

                $payment->update([
                    'transaction_id' => $paymentIntent->id,
                    'payment_status' => 'failed',
                ]);

                $shipment = Shipment::find($payment->shipment_id);

                if ($shipment) {
                    $shipment->update(['status' => 'pending_payment']);

                    $shipment->logs()->create([
                        'status' => 'pending_payment',
                        'location' => $shipment->senderAddress->address ?? 'N/A',
                        'description' => 'Payment failed via Stripe'
                    ]);
                }
                $creator = User::find($shipment->created_by);

                $reason = $paymentIntent->last_payment_error->message ?? null;


                $this->sendPaymentNotification($shipment, $payment, 'FAILED', $reason);

                break;

            case 'checkout.session.expired':

                $session = $event->data->object;

                $shipmentId = $session->client_reference_id
                    ?? $session->metadata->shipment_id
                    ?? null;

                if (!$shipmentId)
                    break;

                $shipment = Shipment::find($shipmentId);
                if (!$shipment)
                    break;

                if ($shipment->status !== 'pending_payment') {
                    $shipment->update(['status' => 'pending_payment']);
                }

                break;
        }

        return response()->json(['status' => 'success']);
    }
}
