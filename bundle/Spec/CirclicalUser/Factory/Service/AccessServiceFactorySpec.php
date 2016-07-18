<?php

namespace Spec\CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use PhpSpec\ObjectBehavior;
use Zend\ServiceManager\ServiceManager;

class AccessServiceFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Factory\Service\AccessServiceFactory');
    }

    function it_creates_its_service(ServiceManager $serviceManager, RoleMapper $roleMapper, GroupPermissionProviderInterface $ruleMapper, UserPermissionProviderInterface $userActionRuleMapper, AuthenticationService $authenticationService, UserMapper $userMapper)
    {
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
        $this->createService($serviceManager)->shouldBeAnInstanceOf(AccessService::class);
    }

    function it_creates_its_service_with_user_identity(ServiceManager $serviceManager, RoleMapper $roleMapper, GroupPermissionProviderInterface $ruleMapper,
                                                       UserPermissionProviderInterface $userActionRuleMapper, AuthenticationService $authenticationService, User $user, UserMapper $userMapper)
    {
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
        $this->createService($serviceManager)->shouldBeAnInstanceOf(AccessService::class);
    }
}
