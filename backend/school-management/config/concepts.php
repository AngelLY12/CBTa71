<?php

use App\Core\Domain\Enum\User\UserRoles;

return [
    'amount' => [
        'min' => 10,
        'max' => 25000,
        'notifications' => [
            'enabled' => true,
            'threshold' => '2500',
            'recipient_roles' => UserRoles::administrationRoles(),
            'channels' => ['mail'],
            'mail' => [
                'title' => 'Alerta: Monto excede límite',
                'intro' => 'Se ha detectado un concepto que excede el límite establecido, se debe verificar'
            ],
        ],
    ],
];
