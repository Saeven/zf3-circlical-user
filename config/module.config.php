<?php

namespace CirclicalUser;

use CirclicalUser\Factory\AbstractDoctrineMapperFactory;
use CirclicalUser\Factory\Listener\AccessListenerFactory;
use CirclicalUser\Factory\Listener\UserEntityListenerFactory;
use CirclicalUser\Factory\Mapper\UserMapperFactory;
use CirclicalUser\Factory\Service\AccessServiceFactory;
use CirclicalUser\Factory\Service\PasswordChecker\PasswordCheckerFactory;
use CirclicalUser\Factory\Strategy\RedirectStrategyFactory;
use CirclicalUser\Factory\Validator\PasswordValidatorFactory;
use CirclicalUser\Factory\View\Helper\ControllerAccessViewHelperFactory;
use CirclicalUser\Factory\View\Helper\RoleAccessViewHelperFactory;
use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Listener\UserEntityListener;
use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Factory\Controller\Plugin\AuthenticationPluginFactory;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Mapper\UserResetTokenMapper;
use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Factory\Service\AuthenticationServiceFactory;
use CirclicalUser\Strategy\RedirectStrategy;
use CirclicalUser\Validator\PasswordValidator;
use CirclicalUser\View\Helper\ControllerAccessViewHelper;
use CirclicalUser\View\Helper\RoleAccessViewHelper;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use function dirname;

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
                    dirname(__DIR__) . '/src/Entity',
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
            PasswordCheckerInterface::class => PasswordCheckerFactory::class,
        ],

        'abstract_factories' => [
            AbstractDoctrineMapperFactory::class,
        ],
    ],

    'validators' => [
        'factories' => [
            PasswordValidator::class => PasswordValidatorFactory::class,
        ],
    ],

    'view_helpers' => [
        'aliases' => [
            'canAccessController' => ControllerAccessViewHelper::class,
            'hasRole' => RoleAccessViewHelper::class,
        ],

        'factories' => [
            ControllerAccessViewHelper::class => ControllerAccessViewHelperFactory::class,
            RoleAccessViewHelper::class => RoleAccessViewHelperFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
        ],
    ],
];
