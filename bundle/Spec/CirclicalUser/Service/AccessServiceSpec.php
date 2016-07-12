<?php

namespace Spec\CirclicalUser\Service;

use CirclicalUser\Entity\Role;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\ActionRuleInterface;
use CirclicalUser\Provider\ActionRuleProviderInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use PhpSpec\ObjectBehavior;

class AccessServiceSpec extends ObjectBehavior
{
    function let(RoleProviderInterface $roleMapper, ActionRuleProviderInterface $ruleProvider, User $user, User $admin,
                 ActionRuleInterface $rule1, ActionRuleInterface $rule2, ActionRuleInterface $rule3 )
    {
        $userRole = new Role();
        $userRole->setId(1);
        $userRole->setName('user');

        $adminRole = new Role();
        $adminRole->setId(2);
        $adminRole->setName('admin');
        $adminRole->setParent($userRole);

        $roleMapper->getAllRoles()->willReturn([$userRole, $adminRole]);

        $rule1->getActions()->willReturn(['consume']);
        $rule1->getRole()->willReturn($userRole);
        $rule1->getResourceClass()->willReturn('string');
        $rule1->getResourceId()->willReturn('beer');
        $rule1->getUserExceptions()->willReturn([]);

        $rule2->getActions()->willReturn(['pourout']);
        $rule2->getRole()->willReturn($adminRole);
        $rule2->getResourceClass()->willReturn('string');
        $rule2->getResourceId()->willReturn('beer');
        $rule2->getUserExceptions()->willReturn([]);

        $rule3->getActions()->willReturn(['buy']);
        $rule3->getRole()->willReturn(null);
        $rule3->getResourceClass()->willReturn('string');
        $rule3->getResourceId()->willReturn('beer');
        $rule3->getUserExceptions()->willReturn([$admin]);


        $ruleProvider->getStringActions('beer')->willReturn([$rule1,$rule2,$rule3]);

        // module-level permissions
        $config = [
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
        ];

        $this->beConstructedWith($config, $roleMapper, $ruleProvider);

        $user->getId()->willReturn(100);
        $user->getRoles()->willReturn([$userRole]);

        $admin->getId()->willReturn(101);
        $admin->getRoles()->willREturn([$adminRole]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Service\AccessService');
    }

    function it_accepts_a_user_with_an_id( User $testUser)
    {
        $testUser->getId()->willReturn(1);
        $this->setUser($testUser);
    }

    function it_rejects_users_with_no_id( User $noUser )
    {
        $noUser->getId()->willReturn(null);
        $this->shouldThrow(UserRequiredException::class)->during('setUser', [$noUser]);
    }

    function it_compiles_roles_properly_1($admin)
    {
        $this->setUser($admin);
        $roles = $this->getRoles();
        $roles->shouldContain('admin');
        $roles->shouldContain('user');
    }

    function it_compiles_roles_properly_2($user)
    {
        $this->setUser($user);
        $roles = $this->getRoles();
        $roles->shouldNotContain('admin');
    }

    function it_performs_user_module_access($user)
    {
        $this->setUser($user);
        $this->canAccessController('Foo\Controller\ThisController')->shouldBe(true);
    }

    function it_performs_guest_module_access()
    {
        $this->canAccessController('Foo\Controller\ThisController')->shouldBe(false);
    }

    function it_rejects_module_access_with_insufficient_rights($user)
    {
        $this->setUser($user);
        $this->canAccessController('Foo\Controller\AdminController')->shouldBe(false);
    }

    function it_permits_relaxed_actions($user)
    {
        $this->setUser($user);
        $this->canAccessAction('Foo\Controller\AdminController', 'oddity')->shouldBe(true);
    }

    function it_accepts_authless_overrides()
    {
        $this->canAccessAction('Foo\Controller\AdminController', 'superodd')->shouldBe(true);
    }

    function it_permits_rigorous_actions($admin)
    {
        $this->setUser($admin);
        $this->canAccessAction('Foo\Controller\ThisController', 'userList')->shouldBe(true);
    }

    function it_gates_rigorous_actions($user)
    {
        $this->setUser($user);
        $this->canAccessAction('Foo\Controller\ThisController', 'userList')->shouldBe(false);
    }

    function it_returns_roles_when_no_user_is_set()
    {
        $this->getRoles()->shouldHaveCount(0);
    }

    function it_returns_roles_when_users_are_set($admin)
    {
        $this->setUser($admin);
        $this->getRoles()->shouldBeArray();
        $this->getRoles()->shouldHaveCount(2);
    }

    function it_accepts_user_verbs($user)
    {
        $this->setUser($user);
        $this->isAllowed('beer','consume')->shouldBe(true);
    }

    function it_declines_user_verbs($user)
    {
        $this->setUser($user);
        $this->isAllowed('beer','pourout')->shouldBe(false);
    }

    function it_accepts_hierarchical_user_verbs($admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer','consume')->shouldBe(true);
    }

    function it_works_in_a_multiverb_situation_a($admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer','pourout')->shouldBe(true);
    }

    function it_uses_user_exceptions($admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer','buy')->shouldBe(true);
    }

    function it_stops_userless_verbs()
    {
        $this->isAllowed('beer','consume')->shouldBe(false);
    }

    function it_declines_nonexistent_verbs($user)
    {
        $this->setUser($user);
        $this->isAllowed('beer','pourout')->shouldBe(false);
    }
}
