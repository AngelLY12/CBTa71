<?php

namespace App\Core\Application\UseCases\Auth;

use App\Core\Application\DTO\Request\General\LoginDTO;
use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;
use App\Core\Infraestructure\Repositories\Command\Stripe\StripeGateway;
use App\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        $user=$this->uqRepo->findUserByEmail($request->email);
        UserValidator::ensureUserIsActive($user);
        if(!$user || !Hash::check($request->password,$user->password)){
            throw new InvalidCredentialsException();
        }
        if ($this->uqRepo->hasRole($user->id, UserRoles::STUDENT->value) && !$user->stripe_customer_id) {
            DB::transaction(function() use ($user) {
                $stripeCustomerId = $this->stripe->createStripeUser($user);
                $this->userRepo->update($user->id, ['stripe_customer_id' => $stripeCustomerId]);
                $user->stripe_customer_id = $stripeCustomerId;
            });
        }
        $userRoles= $this->uqRepo->findUserRoles($user->id);
        $userData=$this->formatUserData($userRoles, $user->fullName(), $user->id);
        $token = $this->userRepo->createToken($user->id,'api-token');
        $refreshToken = $this->userRepo->createRefreshToken($user->id, 'refresh-token');
        return GeneralMapper::toLoginResponse($token,$refreshToken,'Bearer', $userData);
   }

   private function formatUserData(array $roles, string $fullName, int $id): array
   {
        $rolesName = collect($roles)->pluck('name');
        return [
            'id' => $id,
            'fullName' => $fullName,
            'roles' => $rolesName->toArray()
        ];
   }
}
