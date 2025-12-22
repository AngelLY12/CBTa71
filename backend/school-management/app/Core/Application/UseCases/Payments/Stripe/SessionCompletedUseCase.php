<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Traits\HasPaymentSession;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\DomainException;

class SessionCompletedUseCase
{
    public function __construct(
        private UserQueryRepInterface $uqRepo,
        private FinalizeSetupSessionUseCase $finalize
    ) {

    }
    use HasPaymentSession;

    /**
     * @throws \Exception
     */
    public function execute($obj)
    {
        try {
            if (!isset($obj->mode)) {
                logger()->warning("Evento de sesión sin 'mode'. session_id={$obj->id}");
                return true;
            }
            if ($obj->mode === 'payment') {
                $status = EnumMapper::fromStripe($obj->payment_status);
                $this->handlePaymentSession($obj, [
                    'payment_intent_id' => $obj->payment_intent,
                    'status' => $status,
                ]);
                return true;
            }
            if ($obj->mode === 'setup') {
                $user = $this->uqRepo->findUserByEmail($obj->customer_email ?? null);
                if (!$user) {
                    logger()->error("Usuario no encontrado para session_id={$obj->id}");
                    return false;
                }
                return $this->finalize->execute($obj->id, $user);
            }
            logger()->info("Sesión ignorada en webhook. session_id={$obj->id}");
            return true;
        }catch (DomainException $e) {
            logger()->warning("Excepción de dominio en webhook: " . $e->getMessage(), [
                'exception' => get_class($e),
                'use_case' => static::class
            ]);
            return false;

        } catch (\Illuminate\Validation\ValidationException $e) {
            logger()->warning("Excepción de validación en webhook: " . $e->getMessage());
            return false;

        } catch (\Exception $e) {
            logger()->error("Error inesperado en webhook: " . $e->getMessage(), [
                'exception' => get_class($e),
                'use_case' => static::class,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

}
