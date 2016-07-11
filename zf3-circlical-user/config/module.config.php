<?php

namespace CirclicalUser;

use CirclicalUser\Factory\Listener\AccessListenerFactory;
use CirclicalUser\Factory\Service\AccessServiceFactory;
use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Mapper\ActionRuleMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserAuthenticationLogMapper;
use CirclicalUser\Factory\Controller\Plugin\AuthenticationPluginFactory;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Factory\Service\AuthenticationServiceFactory;

return [

    'circlical' => [
        'user' => [
            'providers' => [
                'role' => RoleMapper::class,
                'rule' => ActionRuleMapper::class,
            ],
            'auth' => [
                'crypto_key' => 'sfZGFm1rCc7TgPr9aly3WOtAfbEOb/VafB8L3velkd0=',
                'transient' => false,
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            'circlical_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => __DIR__ . '/../src/CirclicalUser/Entity',
            ],

            'orm_default' => [
                'drivers' => [
                    'CirclicalUser\Entity' => 'circlical_entities',
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'controller_plugins' => [
        'factories' => [
            'auth' => AuthenticationPluginFactory::class,
        ],
    ],

    'service_manager' => [

        'invokables' => [
            UserAuthenticationLogMapper::class => UserAuthenticationLogMapper::class,
        ],

        'factories' => [
            AuthenticationService::class => AuthenticationServiceFactory::class,
            AccessService::class => AccessServiceFactory::class,
            AccessListener::class => AccessListenerFactory::class,
        ],

    ],
];