<?php

namespace Spec\CirclicalUser\Factory\Service\PasswordChecker;

use CirclicalUser\Exception\PasswordStrengthCheckerException;
use CirclicalUser\Factory\Service\PasswordChecker\PasswordCheckerFactory;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Service\PasswordChecker\PasswordNotChecked;
use CirclicalUser\Service\PasswordChecker\Zxcvbn;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

class PasswordCheckerFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PasswordCheckerFactory::class);
    }

    public function it_creates_plain_types(ContainerInterface $container)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'providers' => [
                        'role' => RoleMapper::class,
                    ],
                ],
            ],
        ];
        $container->get('config')->willReturn($config);
        $this->__invoke($container, PasswordCheckerInterface::class, [])->shouldBeAnInstanceOf(PasswordNotChecked::class);
    }

    public function it_creates_specific_types(ContainerInterface $container)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'providers' => [
                        'role' => RoleMapper::class,
                    ],
                    'password_strength_checker' => [
                        'implementation' => \CirclicalUser\Service\PasswordChecker\Zxcvbn::class,
                        'config' => ['required_strength' => 3,],
                    ],
                ],
            ],
        ];
        $container->get('config')->willReturn($config);
        $this->__invoke($container, PasswordCheckerInterface::class, [])->shouldBeAnInstanceOf(Zxcvbn::class);
    }

    public function it_requires_options_when_array_notation_is_used(ContainerInterface $container)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'providers' => [
                        'role' => RoleMapper::class,
                    ],
                    'password_strength_checker' => [
                        'implementation' => \CirclicalUser\Service\PasswordChecker\Zxcvbn::class,
                    ],
                ],
            ],
        ];
        $container->get('config')->willReturn($config);
        $this->shouldThrow(PasswordStrengthCheckerException::class)
            ->during('__invoke', [
                $container,
                PasswordCheckerInterface::class,
                [],
            ]);
    }
}
