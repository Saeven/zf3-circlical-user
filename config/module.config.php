<?php

namespace CirclicalUser;

use CirclicalUser\Factory\AbstractDoctrineMapperFactory;
use CirclicalUser\Factory\Listener\AccessListenerFactory;
use CirclicalUser\Factory\Listener\UserEntityListenerFactory;
use CirclicalUser\Factory\Mapper\UserMapperFactory;
use CirclicalUser\Factory\Service\AccessServiceFactory;
use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Listener\UserEntityListener;
use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserAuthenticationLogMapper;
use CirclicalUser\Factory\Controller\Plugin\AuthenticationPluginFactory;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Factory\Service\AuthenticationServiceFactory;

return [

    /**
     * This module's default configuration block.  There are more values that should be customized inside of
     * circlical.user.local.php.dist.  Check that file out for more.
     */
    'circlical' => [
        'user' => [
            'providers' => [
                'role' => RoleMapper::class,
                'rules' => [
                    'group' => GroupPermissionMapper::class,
                ],
            ],
        ],
    ],

    'doctrine' => [
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    UserEntityListener::class,
                ],
            ],
        ],

        'driver' => [
            'circlical_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => realpath(__DIR__ . '/../src/CirclicalUser/Entity'),
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
            UserEntityListener::class => UserEntityListenerFactory::class,
            UserMapper::class => UserMapperFactory::class,
        ],

        'abstract_factories' => [
            AbstractDoctrineMapperFactory::class,
        ],

    ],
];
