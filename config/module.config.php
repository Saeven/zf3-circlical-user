<?php

namespace CirclicalUser;

use CirclicalUser\Controller\CliController;
use CirclicalUser\Factory\AbstractDoctrineMapperFactory;
use CirclicalUser\Factory\Controller\CliControllerFactory;
use CirclicalUser\Factory\Listener\AccessListenerFactory;
use CirclicalUser\Factory\Listener\UserEntityListenerFactory;
use CirclicalUser\Factory\Mapper\UserMapperFactory;
use CirclicalUser\Factory\Service\AccessServiceFactory;
use CirclicalUser\Factory\Strategy\RedirectStrategyFactory;
use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Listener\UserEntityListener;
use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserAuthenticationLogMapper;
use CirclicalUser\Factory\Controller\Plugin\AuthenticationPluginFactory;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Mapper\UserResetTokenMapper;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Factory\Service\AuthenticationServiceFactory;
use CirclicalUser\Strategy\RedirectStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

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
                    'user' => UserPermissionMapper::class,
                ],
                'reset' => UserResetTokenMapper::class,
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
                'class' => AnnotationDriver::class,
                'paths' => [
                    \dirname(__DIR__) . '/src/CirclicalUser/Entity'
                ],
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

        'factories' => [
            AuthenticationService::class => AuthenticationServiceFactory::class,
            AccessService::class => AccessServiceFactory::class,
            AccessListener::class => AccessListenerFactory::class,
            UserEntityListener::class => UserEntityListenerFactory::class,
            UserMapper::class => UserMapperFactory::class,
            RedirectStrategy::class => RedirectStrategyFactory::class,
        ],

        'abstract_factories' => [
            AbstractDoctrineMapperFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            CliController::class => CliControllerFactory::class,
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [

                'circlical-role-grant' => [
                    'options' => [
                        'route' => 'grant resource-role <roleName> <resourceClass> <resourceId> <verb>',
                        'defaults' => [
                            'controller' => CliController::class,
                            'action' => 'grant-resource-role',
                        ],
                    ],
                ],

                'circlical-user-grant' => [
                    'options' => [
                        'route' => 'grant resource-user <userEmail> <resourceClass> <resourceId> <verb>',
                        'defaults' => [
                            'controller' => CliController::class,
                            'action' => 'grant-resource-user',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
