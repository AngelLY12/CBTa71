<?php

namespace App\Providers;

use App\Core\Domain\Repositories\Command\CareerRepInterface;
use App\Core\Domain\Repositories\Command\DBRepInterface;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Command\RefreshTokenRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Command\StudentDetailReInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\CareerQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use App\Core\Infraestructure\Repositories\Command\EloquentCareerRepository;
use App\Core\Infraestructure\Repositories\Command\EloquentDBRepository;
use App\Core\Infraestructure\Repositories\Command\EloquentRefreshTokenRepository;
use App\Core\Infraestructure\Repositories\Command\EloquentStudentDetailRepository;
use App\Core\Infraestructure\Repositories\Command\EloquentUserRepository;
use App\Core\Infraestructure\Repositories\Command\Payments\EloquentPaymentConceptRepository;
use App\Core\Infraestructure\Repositories\Command\Payments\EloquentPaymentMethodRepository;
use App\Core\Infraestructure\Repositories\Command\Payments\EloquentPaymentRepository;
use App\Core\Infraestructure\Repositories\Command\Stripe\StripeGateway;
use App\Core\Infraestructure\Repositories\Query\EloquentCareerQueryRepository;
use App\Core\Infraestructure\Repositories\Query\EloquentRolesAndPermissionQueryRepository;
use App\Core\Infraestructure\Repositories\Query\EloquentUserQueryRepository;
use App\Core\Infraestructure\Repositories\Query\Payments\EloquentPaymentConceptQueryRepository;
use App\Core\Infraestructure\Repositories\Query\Payments\EloquentPaymentMethodQueryRepository;
use App\Core\Infraestructure\Repositories\Query\Payments\EloquentPaymentQueryRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StripeGatewayInterface::class, StripeGateway::class);
        $this->app->bind(PaymentMethodRepInterface::class, EloquentPaymentMethodRepository::class);
        $this->app->bind(PaymentMethodQueryRepInterface::class, EloquentPaymentMethodQueryRepository::class);
        $this->app->bind(PaymentRepInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(PaymentQueryRepInterface::class, EloquentPaymentQueryRepository::class);
        $this->app->bind(PaymentConceptRepInterface::class, EloquentPaymentConceptRepository::class);
        $this->app->bind(PaymentConceptQueryRepInterface::class, EloquentPaymentConceptQueryRepository::class);
        $this->app->bind(UserRepInterface::class, EloquentUserRepository::class);
        $this->app->bind(UserQueryRepInterface::class, EloquentUserQueryRepository::class);
        $this->app->bind(CareerRepInterface::class, EloquentCareerRepository::class);
        $this->app->bind(CareerQueryRepInterface::class, EloquentCareerQueryRepository::class);
        $this->app->bind(StudentDetailReInterface::class,EloquentStudentDetailRepository::class);
        $this->app->bind(RefreshTokenRepInterface::class,EloquentRefreshTokenRepository::class);
        $this->app->bind(DBRepInterface::class, EloquentDBRepository::class);
        $this->app->singleton(CacheService::class, function () {return new CacheService();});
        $this->app->bind(RolesAndPermissosQueryRepInterface::class,EloquentRolesAndPermissionQueryRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            Storage::extend('google', function($app, $config) {
                $options = [];

                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                if (!empty($config['sharedFolderId'] ?? null)) {
                    $options['sharedFolderId'] = $config['sharedFolderId'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, '/', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            info("Google Drive init failed: " . $e->getMessage());
        }

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        RateLimiter::for('global', function ($request) {
            return Limit::perMinute(30)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
