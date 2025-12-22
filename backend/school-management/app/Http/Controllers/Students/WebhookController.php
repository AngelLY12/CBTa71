<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Stripe\WebhookServiceFacades;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use App\Http\Controllers\Controller;
use App\Jobs\ReconcilePayments;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;
use Stripe\Stripe;

class WebhookController extends Controller

{
    protected WebhookServiceFacades $webhookService;

    public function __construct(WebhookServiceFacades $webhookService){
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
                    return Response::success(null, 'Se completó la sesión');
                    break;
                case 'payment_intent.payment_failed':
                case 'payment_intent.canceled':
                case 'checkout.session.expired':
                    $this->webhookService->handleFailedOrExpiredPayment($obj,$eventType);
                    return Response::success(null, $messageMap[$eventType] ?? 'Evento procesado');
                    break;
                case 'payment_method.attached':
                    $result = $this->webhookService->paymentMethodAttached($obj);
                    if ($result === false) {
                        return Response::success(null, 'El método de pago ya existe');
                    }
                    return Response::success(null, 'Se creó el método de pago');
                    break;
                case 'checkout.session.async_payment_succeeded':
                    $this->webhookService->sessionAsync($obj);
                    ReconcilePayments::dispatch();
                    return Response::success(null, 'Se actualizó el estado del pago');
                    break;
                case 'payment_intent.requires_action':
                    $this->webhookService->requiresAction($obj);
                    return Response::success(null, 'Se notificó correctamente al usuario');
                    break;
                default:
                    return Response::success(null, 'Evento no manejado');
            }

        }  catch (ModelNotFoundException $e) {
            logger()->warning("Recurso no encontrado en webhook: " . $e->getMessage());
            return Response::error('Recurso no encontrado', 404);

        }
        catch (SignatureVerificationException $e) {
            return Response::error('Firma inválida', 400);

        }catch (\Exception $e) {
            logger()->error('Stripe Webhook Error: ' . $e->getMessage());
            return Response::error('Error interno', 500);
        }

    }
}
