<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Traits\HasPaymentSession;
use App\Exceptions\DomainException;

class SessionAsyncCompletedUseCase
{
   use HasPaymentSession;

    public function execute($obj) {
        try {

            $status = EnumMapper::fromStripe($obj->payment_status);
            return $this->handlePaymentSession($obj, [
                'status' => $status,
            ]);
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
