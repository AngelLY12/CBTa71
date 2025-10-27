<?php

namespace App\Core\Application\UseCases;

use App\Core\Application\DTO\Request\General\LoginDTO;
use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Infraestructure\Repositories\Command\Stripe\StripeGateway;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUseCase
{

    public function __construct(
        private UserRepInterface $userRepo,
        private UserQueryRepInterface $uqRepo,
        private StripeGateway $stripe
    )
    {
   }

   public function execute(LoginDTO $request): LoginResponse
   {
        $user=$this->userRepo->findUserByEmail($request->email);
        if(!$user || !Hash::check($request->password,$user->password)){
            throw new InvalidCredentialsException();
        }
        if ($this->uqRepo->hasRole($user, 'student') && !$user->stripe_customer_id) {
            DB::transaction(function() use ($user) {
                $stripeCustomerId = $this->stripe->createStripeUser($user);
                $this->userRepo->update($user, ['stripe_customer_id' => $stripeCustomerId]);
                $user->stripe_customer_id = $stripeCustomerId;
            });
        }
        $token = $this->userRepo->createToken($user,'api-token');
        return GeneralMapper::toLoginResponse($token,'Bearer');
   }
}
