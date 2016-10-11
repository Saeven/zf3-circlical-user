<?php

namespace Spec\CirclicalUser\Service;

use CirclicalUser\Entity\Role;
use CirclicalUser\Exception\GuardConfigurationException;
use CirclicalUser\Exception\InvalidRoleException;
use CirclicalUser\Exception\UnknownResourceTypeException;
use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserPermissionInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\GroupPermissionInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AccessServiceSpec extends ObjectBehavior
{
    function let(RoleProviderInterface $roleMapper, GroupPermissionProviderInterface $groupRules, UserPermissionProviderInterface $userRules,
                 User $user, User $admin,
                 GroupPermissionInterface $rule1, GroupPermissionInterface $rule2, GroupPermissionInterface $rule3,
                 UserPermissionInterface $userRule1, UserPermissionInterface $userRule2, UserPermissionInterface $userRule3,
                 ResourceInterface $resourceObject, GroupPermissionInterface $groupActionRule, UserMapper $userMapper, User $someObject)
    {

        $userRole = new Role();
        $userRole->setId(1);
        $userRole->setName('user');

        $adminRole = new Role();
        $adminRole->setId(2);
        $adminRole->setName('admin');
        $adminRole->setParent($userRole);

        $roleMapper->getAllRoles()->willReturn([$userRole, $adminRole]);
        $roleMapper->getRoleWithName(Argument::any())->willReturn(null);
        $roleMapper->getRoleWithName('admin')->willReturn($adminRole);


        /*
         * Rule 1: Users can consume beer
         */
        $rule1->getActions()->willReturn(['consume']);
        $rule1->getRole()->willReturn($userRole);
        $rule1->getResourceClass()->willReturn('string');
        $rule1->getResourceId()->willReturn('beer');
        $rule1->can(Argument::type('string'))->willReturn(false);
        $rule1->can('consume')->willReturn(true);

        /*
         * Rule 2: Admins can pour beer
         */
        $rule2->getActions()->willReturn(['pour']);
        $rule2->getRole()->willReturn($adminRole);
        $rule2->getResourceClass()->willReturn('string');
        $rule2->getResourceId()->willReturn('beer');
        $rule2->can(Argument::type('string'))->willReturn(false);
        $rule2->can('pour')->willReturn(true);

        /*
         * Rule 3: Guests can look beer
         */
        $rule3->getActions()->willReturn(['look']);
        $rule3->getRole()->willReturn(null);
        $rule3->getResourceClass()->willReturn('string');
        $rule3->getResourceId()->willReturn('beer');
        $rule3->can(Argument::type('string'))->willReturn(false);
        $rule3->can('look')->willReturn(true);

        /*
         * Rule 4: Admin user can choose beer
         */
        $userRule1->getActions()->willReturn(['buy']);
        $userRule1->getResourceClass()->willReturn('string');
        $userRule1->getResourceId()->willReturn('beer');
        $userRule1->getUser()->willReturn($admin);
        $userRule1->can(Argument::type('string'))->willReturn(false);
        $userRule1->can('buy')->willReturn(true);

        $userRule2->getActions()->willReturn(['buy']);
        $userRule2->getResourceClass()->willReturn('string');
        $userRule2->getResourceId()->willReturn('beer');
        $userRule2->getUser()->willReturn($user);
        $userRule2->can(Argument::type('string'))->willReturn(false);
        $userRule2->can('buy')->willReturn(true);

        $userRule3->getActions()->willReturn(['bar']);
        $userRule3->getResourceClass()->willReturn('ResourceObject');
        $userRule3->getResourceId()->willReturn('1234');
        $userRule3->getUser()->willReturn($user);
        $userRule3->addAction('foo')->willReturn(null);
        $userRule3->can(Argument::type('string'))->willReturn(false);
        $userRule3->can('bar')->willReturn(true);

        $resourceObject->getClass()->willReturn("ResourceObject");
        $resourceObject->getId()->willReturn("1234");

        $groupActionRule->getResourceClass()->willReturn("ResourceObject");
        $groupActionRule->getResourceId()->willReturn("1234");
        $groupActionRule->getRole()->willReturn('user');
        $groupActionRule->getActions()->willReturn(['bar']);
        $groupActionRule->can(Argument::type('string'))->willReturn(false);
        $groupActionRule->can('bar')->willReturn(true);

        $userRules->getUserPermission(Argument::type('string'), Argument::any())->willReturn(null);
        $userRules->getUserPermission('beer', $admin)->willReturn($userRule1);
        $userRules->create($user, 'string', 'beer', ['buy'])->willReturn($userRule2);
        $userRules->save($userRule2)->willReturn(null);
        $userRules->getResourceUserPermission($resourceObject, $user)->willReturn($userRule3);
        $userRules->update(Argument::any())->willReturn(null);

        // to test a case, where a user implementation returns complete garbage
        $userRules->getUserPermission('badresult', $user)->willReturn($someObject);


        $groupRules->getPermissions('beer')->willReturn([$rule1, $rule2, $rule3]);
        $groupRules->getResourcePermissions($resourceObject)->willReturn([$groupActionRule]);
        $groupRules->getResourcePermissionsByClass('ResourceObject')->willReturn([$groupActionRule]);


        $config = [
            'Foo' => [
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
                    'Foo\Controller\FreeForAll' => [
                        'default' => [],
                    ],
                ],
            ],
        ];

        $this->beConstructedWith($config, $roleMapper, $groupRules, $userRules, $userMapper);

        $user->getId()->willReturn(100);
        $user->getRoles()->willReturn([$userRole]);
        $user->addRole(Argument::any())->willReturn(null);

        $admin->getId()->willReturn(101);
        $admin->getRoles()->willReturn([$adminRole]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Service\AccessService');
    }

    function it_requires_an_array_as_default_config()
    {
        $config = [
            'Foo' => [
                'controllers' => [
                    'Foo\Controller\ThisController' => [
                        'default' => 'user',
                    ],
                ],
            ],
        ];
        $roleMapper = new RoleMapper();
        $groupMapper = new GroupPermissionMapper();
        $userPermissionMapper = new UserPermissionMapper();
        $userMapper = new UserMapper('Foo');
        $this->shouldThrow(GuardConfigurationException::class)->during('__construct', [$config, $roleMapper, $groupMapper, $userPermissionMapper, $userMapper]);
    }

    function it_requires_an_array_as_action_config()
    {
        $config = [
            'Foo' => [
                'controllers' => [
                    'Foo\Controller\ThisController' => [
                        'default' => ['user'],
                        'actions' => 'badConfig',
                    ],
                ],
            ],
        ];
        $roleMapper = new RoleMapper();
        $groupMapper = new GroupPermissionMapper();
        $userPermissionMapper = new UserPermissionMapper();
        $userMapper = new UserMapper('Foo');
        $this->shouldThrow(GuardConfigurationException::class)->during('__construct', [$config, $roleMapper, $groupMapper, $userPermissionMapper, $userMapper]);
    }

    function it_requires_an_array_as_action_roles_config()
    {
        $config = [
            'Foo' => [
                'controllers' => [
                    'Foo\Controller\ThisController' => [
                        'default' => ['user'],
                        'actions' => [
                            'verb' => 'roleShouldBeArray',
                        ],
                    ],
                ],
            ],
        ];
        $roleMapper = new RoleMapper();
        $groupMapper = new GroupPermissionMapper();
        $userPermissionMapper = new UserPermissionMapper();
        $userMapper = new UserMapper('Foo');
        $this->shouldThrow(GuardConfigurationException::class)->during('__construct', [$config, $roleMapper, $groupMapper, $userPermissionMapper, $userMapper]);
    }

    function it_accepts_a_user_with_an_id(User $testUser)
    {
        $testUser->getId()->willReturn(1);
        $this->setUser($testUser);
    }

    function it_rejects_users_with_no_id(User $noUser)
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

    function it_adds_roles($user)
    {
        $this->setUser($user);
        $this->hasRoleWithName('admin')->shouldBe(false);
        $this->addRoleByName('admin');
        $this->hasRoleWithName('admin')->shouldBe(true);
    }

    function it_compiles_roles_properly_2($user)
    {
        $this->setUser($user);
        $roles = $this->getRoles();
        $roles->shouldNotContain('admin');
    }

    function it_dies_when_you_add_roles_to_nobody()
    {
        $this->shouldThrow(UserRequiredException::class)->during('addRoleByName', ['admin']);
    }

    function it_bails_if_you_try_to_add_roles_already_added($admin, $roleMapper)
    {
        $this->setUser($admin);
        $roleMapper->getRoleWithName('admin')->shouldNotBeCalled();
        $this->addRoleByName('admin');
    }

    function it_bails_if_you_try_to_add_roles_that_dont_exist($admin)
    {
        $this->setUser($admin);
        $this->shouldThrow(InvalidRoleException::class)->during('addRoleByName', ['whatisthis']);
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

    function it_returns_false_when_controllers_are_not_configured()
    {
        $this->canAccessController('NotHere')->shouldBe(false);
    }

    function it_returns_true_when_no_controller_roles_are_configured()
    {
        $this->canAccessController('Foo\Controller\FreeForAll')->shouldBe(true);
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
        $this->isAllowed('beer', 'consume')->shouldBe(true);
    }

    function it_declines_user_verbs($user)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'pourout')->shouldBe(false);
    }

    function it_accepts_hierarchical_user_verbs($admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer', 'consume')->shouldBe(true);
    }

    function it_works_in_a_multiverb_situation_a($admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer', 'pour')->shouldBe(true);
    }

    function it_uses_user_exceptions($admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer', 'buy')->shouldBe(true);
    }

    function it_stops_userless_verbs()
    {
        $this->isAllowed('beer', 'consume')->shouldBe(false);
    }

    function it_declines_nonexistent_verbs($user)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'pourout')->shouldBe(false);
    }

    function it_defers_to_controllers_when_actions_are_not_configured($user)
    {
        $this->setUser($user);
        $this->canAccessAction('Foo\Controller\ThisController', 'notConfigured')->shouldBe(true);
    }

    function it_can_grant_users_access_to_strings($user, $userRules, $userRule2)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'buy')->shouldBe(false);
        $userRules->create($user, 'string', 'beer', ['buy'])->shouldBeCalled();
        $userRules->save($userRule2)->shouldBeCalled();
        $this->grantUserAccess('beer', 'buy');
    }

    function it_can_grant_users_access_to_new_resources($user, $resourceObject)
    {
        $this->setUser($user);
        $this->isAllowed($resourceObject, 'foo')->shouldBe(false);
        $this->grantUserAccess($resourceObject, 'foo');
    }

    function it_can_grant_users_access_to_existing_resources($user, $resourceObject)
    {
        $this->setUser($user);
        $this->isAllowed($resourceObject, 'foo')->shouldBe(false);
        $this->grantUserAccess($resourceObject, 'foo');
    }

//    function it_handles_bad_user_permission_provider_implementations($user)
//    {
//        $this->setUser($user);
//        $this->grantUserAccess('badresult', 'foo');
//    }

    function it_throws_exceptions_when_group_actions_are_requested_for_bad_resources()
    {
        $this->shouldThrow(UnknownResourceTypeException::class)->during('getGroupPermissions', [null]);
    }

    function it_throws_exceptions_when_no_user_is_set_and_user_actions_are_requested()
    {
        $this->shouldThrow(UserRequiredException::class)->during('getUserPermission', [null]);
    }

    function it_throws_exceptions_when_user_actions_are_requested_for_bad_resources($user)
    {
        $this->setUser($user);
        $this->shouldThrow(UnknownResourceTypeException::class)->during('getUserPermission', [null]);
    }

    function it_returns_allowed_rules_by_class($user, $groupActionRule)
    {
        // 1234, is the ID of the sole mocked object whose class is ResourceObject
        $this->setUser($user);
        $this->listAllowedByClass('ResourceObject')->shouldContain("1234");
    }

}