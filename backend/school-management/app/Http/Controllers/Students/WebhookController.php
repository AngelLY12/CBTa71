<?php

namespace App\Http\Controllers\Students;

use Illuminate\Http\Request;
use App\Models\Payment;
use Stripe\Webhook;
use App\Http\Controllers\Controller;
use App\Jobs\ReconcilePayments;
use App\Notifications\PaymentFailedNotification;
use App\Services\PaymentSystem\WebhookService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Stripe\Stripe;

class WebhookController extends Controller

{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService){
        $this->webhookService=$webhookService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook');
        logger()->info("Payload: ".$payload);
        logger()->info("Signature: ".$sigHeader);
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            $obj = $event->data->object;
            $eventType=$event->type;

            $messageMap = [
                'payment_intent.payment_failed' => 'El pago falló',
                'payment_intent.canceled' => 'El pago fue cancelado',
                'checkout.session.expired' => 'La sesión de pago expiró'
            ];
            switch($eventType){

                case 'checkout.session.completed':
                    $this->webhookService->sessionCompleted($obj);
                    if($obj->payment_status==='paid'){
                        ReconcilePayments::dispatch();
                    }
                    return response()->json(['success' => true,'message'=>'Se completo la sesión']);
                    break;
                case 'payment_intent.payment_failed':
                case 'payment_intent.canceled':
                case 'checkout.session.expired':
                    $this->webhookService->handleFailedOrExpiredPayment($obj,$eventType);
                    return response()->json(['success' => true,'message'=>$messageMap[$eventType] ?? 'Evento procesado']);
                    break;
                case 'payment_method.attached':
                    $result = $this->webhookService->paymentMethodAttached($obj);
                    if ($result === false) {
                        return response()->json(['success' => true, 'message' => 'El método de pago ya existe']);
                    }
                    return response()->json(['success' => true, 'message' => 'Se creó el método de pago']);
                    break;
                case 'checkout.session.async_payment_succeeded':
                    $this->webhookService->sessionAsync($obj);
                    ReconcilePayments::dispatch();
                    return response()->json(['success' => true,'message'=>'Se actualizo el estado del pago']);
                    break;
                case 'payment_intent.requires_action':
                    $this->webhookService->requiresAction($obj);
                    return response()->json(['success' => true,'message'=>'Se notifico correctamente al usuario']);
                    break;
                default:
                    return response()->json(['success' => true, 'message' => 'Evento no manejado']);
            }

        }  catch (ModelNotFoundException $e) {
            logger()->warning("Recurso no encontrado en webhook: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Recurso no encontrado'], 404);

        } catch (\Exception $e) {
            logger()->error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

}
