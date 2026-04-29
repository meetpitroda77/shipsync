<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ShipmentStatusNotification;
use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\Payment;
use Stripe\Webhook;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
class StripeWebhookController extends Controller
{
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

            case 'payment_intent.succeeded':

                $paymentIntent = $event->data->object;

                $shipmentId = $paymentIntent->metadata->shipment_id ?? null;

                if (!$shipmentId)
                    break;

                $shipment = Shipment::find($shipmentId);
                if (!$shipment)
                    break;

                if ($shipment->status === 'pending_assigned')
                    break;

                Payment::where('shipment_id', $shipment->id)->update([
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount / 100,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                $shipment->update(['status' => 'pending_assigned']);

                $shipment->logs()->create([
                    'status' => 'pending_assigned',
                    'description' => 'Payment successful via PaymentIntent'
                ]);
                $creator = User::find($shipment->created_by);
                if ($creator) {
                    $creator->notify(new ShipmentStatusNotification($shipment));
                }


                break;
            case 'payment_intent.payment_failed':



                $paymentIntent = $event->data->object;

                Stripe::setApiKey(env('STRIPE_SECRET'));

                try {
                    $sessions = StripeSession::all([
                        'payment_intent' => $paymentIntent->id,
                        'limit' => 1,
                    ]);

                    if (empty($sessions->data)) {
                        \Log::error('No session found for payment intent');
                        break;
                    }

                    $session = $sessions->data[0];

                    $shipmentId = $session->client_reference_id;



                    $shipment = Shipment::find($shipmentId);

                    Payment::where('shipment_id', $shipment->id)->update([
                        'transaction_id' => $paymentIntent->id,
                        'payment_status' => 'failed',
                    ]);

                    $shipment->update(['status' => 'pending_payment']);

                    $shipment->logs()->create([
                        'status' => 'pending_payment',
                        'location' => $shipment->senderAddress->address ?? 'N/A',
                        'description' => 'Payment failed via Stripe'
                    ]);

                    $creator = User::find($shipment->created_by);
                    if ($creator) {
                        $creator->notify(new ShipmentStatusNotification($shipment));
                    }
                    \Log::info('Payment failed handled', [
                        'shipment_id' => $shipment->id,
                        'payment_intent' => $paymentIntent->id
                    ]);


                } catch (\Exception $e) {
                    \Log::error('Failed handling payment_failed', [
                        'error' => $e->getMessage()
                    ]);
                }

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
                    $shipment->update([
                        'status' => 'pending_payment'
                    ]);
                }

              

                \Log::info('Checkout session expired handled', [
                    'shipment_id' => $shipment->id,
                    'session_id' => $session->id
                ]);

                break;
        }



        return response()->json(['status' => 'success']);
    }
}