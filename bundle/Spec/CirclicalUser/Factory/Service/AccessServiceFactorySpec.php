<?php

namespace Spec\CirclicalUser\Factory\Service;

use CirclicalUser\Exception\InvalidRoleException;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use PhpSpec\ObjectBehavior;
use Laminas\ServiceManager\ServiceManager;
use CirclicalUser\Factory\Service\AccessServiceFactory;

class AccessServiceFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AccessServiceFactory::class);
    }

    function it_creates_its_service(
        ServiceManager $serviceManager,
        RoleMapper $roleMapper,
        GroupPermissionProviderInterface $ruleMapper,
        UserPermissionProviderInterface $userActionRuleMapper,
        AuthenticationService $authenticationService,
        UserMapper $userMapper
    ) {
        $config = [

            'circlical' => [
                'user' => [
                    'providers' => [
                        'role' => RoleMapper::class,
                        'rule' => [
                            'group' => GroupPermissionProviderInterface::class,
                            'user' => UserPermissionProviderInterface::class,
                        ],
                    ],
                    'auth' => [
                        'crypto_key' => 'sfZGFm1rCc7TgPr9aly3WOtAfbEOb/VafB8L3velkd0=',
                        'transient' => false,
                    ],
                    'guards' => [
                        'Foo' => [
                            // controller-level-permissions
                            'controllers' => [
                                'Foo\Controller\ThisController' => [
                                    'default' => ['user'],
                                    'actions' => [
                                        'index' => ['user'],
                                        'userList' => ['admin'],
                                    ],
                                ],
                                'Foo\Controller\AdminController' => [
                                    'default' => ['admin'],
                                    'actions' => [
                                        'oddity' => ['user'],
                                        'superodd' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $serviceManager->get('config')->willReturn($config);
        $serviceManager->get(RoleMapper::class)->willReturn($roleMapper);
        $serviceManager->get(GroupPermissionProviderInterface::class)->willReturn($ruleMapper);
        $serviceManager->get(UserPermissionProviderInterface::class)->willReturn($userActionRuleMapper);
        $serviceManager->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);
        $this->__invoke($serviceManager, AccessService::class)->shouldBeAnInstanceOf(AccessService::class);
    }

    function it_creates_its_service_with_user_identity(
        ServiceManager $serviceManager,
        RoleMapper $roleMapper,
        GroupPermissionProviderInterface $ruleMapper,
        UserPermissionProviderInterface $userActionRuleMapper,
        AuthenticationService $authenticationService,
        User $user,
        UserMapper $userMapper
    ) {
        $config = [

            'circlical' => [
                'user' => [
                    'providers' => [
                        'role' => RoleMapper::class,
                        'rule' => [
                            'group' => GroupPermissionProviderInterface::class,
                            'user' => UserPermissionProviderInterface::class,
                        ],
                    ],
                    'guards' => [
                        'Foo' => [
                            // controller-level-permissions
                            'controllers' => [
                                'Foo\Controller\ThisController' => [
                                    'default' => ['user'],
                                    'actions' => [
                                        'index' => ['user'],
                                        'userList' => ['admin'],
                                    ],
                                ],
                                'Foo\Controller\AdminController' => [
                                    'default' => ['admin'],
                                    'actions' => [
                                        'oddity' => ['user'],
                                        'superodd' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $user->getId()->willReturn(1);
        $authenticationService->getIdentity()->willReturn($user);

        $serviceManager->get('config')->willReturn($config);
        $serviceManager->get(RoleMapper::class)->willReturn($roleMapper);
        $serviceManager->get(GroupPermissionProviderInterface::class)->willReturn($ruleMapper);
        $serviceManager->get(UserPermissionProviderInterface::class)->willReturn($userActionRuleMapper);
        $serviceManager->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);
        $this->__invoke($serviceManager, AccessService::class)->shouldBeAnInstanceOf(AccessService::class);
    }


    function it_should_not_panic_when_guards_are_not_defined(
        ServiceManager $serviceManager,
        RoleMapper $roleMapper,
        GroupPermissionProviderInterface $ruleMapper,
        UserPermissionProviderInterface $userActionRuleMapper,
        AuthenticationService $authenticationService,
        User $user,
        UserMapper $userMapper
    ) {
        $config = [
            'circlical' => [
                'user' => [
                    'providers' => [
                        'role' => RoleMapper::class,
                        'rule' => [
                            'group' => GroupPermissionProviderInterface::class,
                            'user' => UserPermissionProviderInterface::class,
                        ],
                    ],
                ],
            ],
        ];

        $user->getId()->willReturn(1);
        $authenticationService->getIdentity()->willReturn($user);

        $serviceManager->get('config')->willReturn($config);
        $serviceManager->get(RoleMapper::class)->willReturn($roleMapper);
        $serviceManager->get(GroupPermissionProviderInterface::class)->willReturn($ruleMapper);
        $serviceManager->get(UserPermissionProviderInterface::class)->willReturn($userActionRuleMapper);
        $serviceManager->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);

        $this->shouldThrow(\Exception::class)->during('__invoke', [$serviceManager, AccessService::class]);
    }

    function it_can_load_in_superadmins_and_fail_without_complaining_if_the_role_does_not_exist(
        ServiceManager $serviceManager,
        RoleMapper $roleMapper,
        GroupPermissionProviderInterface $ruleMapper,
        UserPermissionProviderInterface $userActionRuleMapper,
        AuthenticationService $authenticationService,
        User $user,
        UserMapper $userMapper
    ) {
        $config = [
            'circlical' => [
                'user' => [
                    'access' => [

                        'superadmin' => [

                            /**
                             * Superadmin role name, will be sought in the DB when configured.  Please ensure that your
                             * Role entity with this name exists prior to configuration.
                             */
                            'role_name' => 'superadmin',

                            /**
                             * If the role was named in config, yet the entity was missing, do we crash and burn?
                             */
                            'throw_exception_when_missing' => false,
                        ],
                    ],

                    'providers' => [
                        'role' => RoleMapper::class,
                        'rule' => [
                            'group' => GroupPermissionProviderInterface::class,
                            'user' => UserPermissionProviderInterface::class,
                        ],
                    ],

                    'guards' => [
                        'Foo' => [
                            // controller-level-permissions
                            'controllers' => [],
                        ],
                    ],
                ],
            ],
        ];

        $user->getId()->willReturn(1);
        $authenticationService->getIdentity()->willReturn($user);

        $serviceManager->get('config')->willReturn($config);
        $serviceManager->get(RoleMapper::class)->willReturn($roleMapper);
        $serviceManager->get(GroupPermissionProviderInterface::class)->willReturn($ruleMapper);
        $serviceManager->get(UserPermissionProviderInterface::class)->willReturn($userActionRuleMapper);
        $serviceManager->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);

        $roleMapper->getRoleWithName('superadmin')->willReturn(null);

        /** @var AccessService $object */
        $object = $this->__invoke($serviceManager, AccessService::class);
        $object->shouldBeAnInstanceOf(AccessService::class);
        $object->isSuperAdmin()->shouldBe(false);
    }

    function it_will_throw_exceptions_when_superadmin_roles_do_not_exist_and_throws_are_configured(
        ServiceManager $serviceManager,
        RoleMapper $roleMapper,
        GroupPermissionProviderInterface $ruleMapper,
        UserPermissionProviderInterface $userActionRuleMapper,
        AuthenticationService $authenticationService,
        User $user,
        UserMapper $userMapper
    ) {
        $config = [
            'circlical' => [
                'user' => [
                    'access' => [

                        'superadmin' => [

                            /**
                             * Superadmin role name, will be sought in the DB when configured.  Please ensure that your
                             * Role entity with this name exists prior to configuration.
                             */
                            'role_name' => 'superadmin',

                            /**
                             * If the role was named in config, yet the entity was missing, do we crash and burn?
                             */
                            'throw_exception_when_missing' => true,
                        ],
                    ],

                    'providers' => [
                        'role' => RoleMapper::class,
                        'rule' => [
                            'group' => GroupPermissionProviderInterface::class,
                            'user' => UserPermissionProviderInterface::class,
                        ],
                    ],

                    'guards' => [
                        'Foo' => [
                            // controller-level-permissions
                            'controllers' => [],
                        ],
                    ],
                ],
            ],
        ];

        $user->getId()->willReturn(1);
        $authenticationService->getIdentity()->willReturn($user);

        $serviceManager->get('config')->willReturn($config);
        $serviceManager->get(RoleMapper::class)->willReturn($roleMapper);
        $serviceManager->get(GroupPermissionProviderInterface::class)->willReturn($ruleMapper);
        $serviceManager->get(UserPermissionProviderInterface::class)->willReturn($userActionRuleMapper);
        $serviceManager->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);

        $roleMapper->getRoleWithName('superadmin')->willReturn(null);

        $this->shouldThrow(InvalidRoleException::class)->during('__invoke', [$serviceManager, AccessService::class]);
    }

    function it_loads_superadmins_when_everything_is_wired_properly(
        ServiceManager $serviceManager,
        RoleMapper $roleMapper,
        GroupPermissionProviderInterface $ruleMapper,
        UserPermissionProviderInterface $userActionRuleMapper,
        AuthenticationService $authenticationService,
        User $user,
        UserMapper $userMapper,
        RoleInterface $superAdmin,
        User $otherUser
    ) {
        $config = [
            'circlical' => [
                'user' => [
                    'access' => [

                        'superadmin' => [

                            /**
                             * Superadmin role name, will be sought in the DB when configured.  Please ensure that your
                             * Role entity with this name exists prior to configuration.
                             */
                            'role_name' => 'superadmin',

                            /**
                             * If the role was named in config, yet the entity was missing, do we crash and burn?
                             */
                            'throw_exception_when_missing' => false,
                        ],
                    ],

                    'providers' => [
                        'role' => RoleMapper::class,
                        'rule' => [
                            'group' => GroupPermissionProviderInterface::class,
                            'user' => UserPermissionProviderInterface::class,
                        ],
                    ],

                    'guards' => [
                        'Foo' => [
                            // controller-level-permissions
                            'controllers' => [],
                        ],
                    ],
                ],
            ],
        ];

        $user->getId()->willReturn(1);
        $user->hasRole($superAdmin)->willReturn(true);

        $authenticationService->getIdentity()->willReturn($user);

        $serviceManager->get('config')->willReturn($config);
        $serviceManager->get(RoleMapper::class)->willReturn($roleMapper);
        $serviceManager->get(GroupPermissionProviderInterface::class)->willReturn($ruleMapper);
        $serviceManager->get(UserPermissionProviderInterface::class)->willReturn($userActionRuleMapper);
        $serviceManager->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);

        $roleMapper->getRoleWithName('superadmin')->willReturn($superAdmin);

        $object = $this->__invoke($serviceManager, AccessService::class);
        $object->shouldBeAnInstanceOf(AccessService::class);
        $object->isSuperAdmin()->shouldBe(true);
    }
}
