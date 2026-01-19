<?php

namespace App\Core\Application\UseCases\Auth;

use App\Core\Application\DTO\Request\General\LoginDTO;
use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;
use App\Core\Infraestructure\Repositories\Stripe\StripeGateway;
use App\Exceptions\Unauthorized\InvalidCredentialsException;
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

        $passwordValid = $user ? Hash::check($request->password, $user->password) : false;

        if (!$user || !$passwordValid) {
           throw new InvalidCredentialsException();
        }
        $this->verifyStripeCustomerId($user);
       $hasUnreadNotifications = $this->uqRepo->userHasUnreadNotifications($user->id);
       $userData=$this->formatUserData($user->getRoleNames(), $user->fullName(), $user->id, $hasUnreadNotifications);
        $token = $this->userRepo->createToken($user->id,'api-token');
        $refreshToken = $this->userRepo->createRefreshToken($user->id, 'refresh-token');
        return GeneralMapper::toLoginResponse($token,$refreshToken,'Bearer', $userData);
   }

   private function formatUserData(array $roles, string $fullName, int $id, bool $hasUnreadNotifications): array
   {
       return [
            'id' => $id,
            'fullName' => $fullName,
            'roles' => $roles,
            'hasUnreadNotifications' => $hasUnreadNotifications
        ];
   }

   private function verifyStripeCustomerId(User $user): void
   {
       if ($user->isStudent() && !$user->stripe_customer_id) {
           $stripeCustomerId = $this->stripe->createStripeUser($user);
           $this->userRepo->update($user->id, ['stripe_customer_id' => $stripeCustomerId]);
           $user->stripe_customer_id = $stripeCustomerId;
       }
   }
}
