<?php

namespace Spec\CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\AuthenticationMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Service\AuthenticationService;
use PhpSpec\ObjectBehavior;
use Zend\ServiceManager\ServiceManager;

class AuthenticationServiceFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Factory\Service\AuthenticationServiceFactory');
    }

    function it_creates_its_service(ServiceManager $serviceManager, AuthenticationMapper $authenticationMapper, UserMapper $userMapper)
    {

        $config = [

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
        $serviceManager->get(AuthenticationMapper::class)->willReturn($authenticationMapper);
        $serviceManager->get(UserMapper::class)->willReturn($userMapper);

        $this->__invoke($serviceManager, AuthenticationService::class)->shouldBeAnInstanceOf(AuthenticationService::class);

    }
}
