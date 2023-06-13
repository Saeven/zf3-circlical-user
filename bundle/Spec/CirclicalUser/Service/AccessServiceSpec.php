<?php

namespace Spec\CirclicalUser\Service;

use CirclicalUser\Entity\Role;
use CirclicalUser\Exception\ExistingAccessException;
use CirclicalUser\Exception\GuardConfigurationException;
use CirclicalUser\Exception\GuardExpectedException;
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
use CirclicalUser\Service\AccessService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AccessServiceSpec extends ObjectBehavior
{
    private $superAdminRole;

    function let(
        RoleProviderInterface $roleMapper,
        GroupPermissionProviderInterface $groupRules,
        UserPermissionProviderInterface $userRules,
        User $user,
        User $admin,
        GroupPermissionInterface $rule1,
        GroupPermissionInterface $rule2,
        GroupPermissionInterface $rule3,
        UserPermissionInterface $userRule1,
        UserPermissionInterface $userRule2,
        UserPermissionInterface $userRule3,
        UserPermissionInterface $userRule4,
        ResourceInterface $resourceObject,
        GroupPermissionInterface $groupActionRule,
        UserMapper $userMapper,
        User $someObject,
        User $superAdmin
    ) {
        $userRole = new Role('user', null);
        $userRole->setId(1);

        $adminRole = new Role('admin', $userRole);
        $adminRole->setId(2);

        $this->superAdminRole = new Role('superadmin', null);
        $this->superAdminRole->setId(3);

        $roleMapper->getAllRoles()->willReturn([$userRole, $adminRole]);
        $roleMapper->getRoleWithName(Argument::any())->willReturn(null);
        $roleMapper->getRoleWithName('admin')->willReturn($adminRole);
        $roleMapper->getRoleWithName('user')->willReturn($userRole);
        $roleMapper->getRoleWithName('superadmin')->willReturn($this->superAdminRole);


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
        $userRule3->can(Argument::type('string'))->willReturn(false);
        $userRule3->can('bar')->willReturn(true);

        $userRule4->getActions()->willReturn(['save']);
        $userRule4->getResourceClass()->willReturn('string');
        $userRule4->getResourceId()->willReturn('complex');
        $userRule4->getUser()->willReturn($user);
        $userRule4->can(Argument::type('string'))->willReturn(false);
        $userRule4->can('save')->willReturn(true);

        $resourceObject->getClass()->willReturn("ResourceObject");
        $resourceObject->getId()->willReturn("1234");

        $groupActionRule->getResourceClass()->willReturn("ResourceObject");
        $groupActionRule->getResourceId()->willReturn("1234");
        $groupActionRule->getRole()->willReturn($userRole);
        $groupActionRule->getActions()->willReturn(['bar']);
        $groupActionRule->can(Argument::type('string'))->willReturn(false);
        $groupActionRule->can('bar')->willReturn(true);


        $userRules->getUserPermission(Argument::type('string'), Argument::any())->willReturn(null);
        $userRules->getUserPermission('beer', $admin)->willReturn($userRule1);
        $userRules->getUserPermission('complex', $user)->willReturn($userRule4);
        $userRules->create($user, 'string', 'beer', ['buy'])->willReturn($userRule2);
        $userRules->save($userRule2)->willReturn(null);
        $userRules->getResourceUserPermission($resourceObject, $user)->willReturn($userRule3);
        $userRules->update(Argument::any())->willReturn(null);

        // to test a case, where a user implementation returns complete garbage
        $userRules->getUserPermission('badresult', $user)->willReturn($someObject);

        $groupRules->getPermissions('beer')->willReturn([$rule1, $rule2, $rule3]);
        $groupRules->getPermissions('complex')->willReturn([]);
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
                        'actions' => [
                            'get-name' => ['user'],
                        ],
                    ],
                    'Foo\Controller\IndexController' => [
                        'default' => ['user'],
                        'actions' => [
                            'home' => [],
                            'login' => [],
                        ],
                    ],
                    'Admin\Controller\ComplexController' => [
                        'default' => ['user'],
                        'actions' => [
                            'save' => [
                                AccessService::GUARD_ACTION => 'save',
                                AccessService::GUARD_RESOURCE => 'complex',
                            ],
                            'delete' => [
                                AccessService::GUARD_ROLE => 'admin',
                                AccessService::GUARD_ACTION => 'save',
                                AccessService::GUARD_RESOURCE => 'complex',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->beConstructedWith($config, $roleMapper, $groupRules, $userRules, $userMapper, null);

        $user->getId()->willReturn(100);
        $user->getRoles()->willReturn([$userRole]);
        $user->addRole(Argument::any())->willReturn(null);

        $admin->getId()->willReturn(101);
        $admin->getRoles()->willReturn([$adminRole]);

        $superAdmin->getId()->willReturn(102);
        $superAdmin->getRoles()->willReturn([$this->superAdminRole, $userRole]);
        $superAdmin->hasRole($this->superAdminRole)->willReturn(true);
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
        $this->shouldThrow(GuardConfigurationException::class)->during('__construct', [$config, $roleMapper, $groupMapper, $userPermissionMapper, $userMapper, null]);
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
        $this->shouldThrow(GuardConfigurationException::class)->during('__construct', [$config, $roleMapper, $groupMapper, $userPermissionMapper, $userMapper, null]);
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
        $this->shouldThrow(GuardConfigurationException::class)->during('__construct', [$config, $roleMapper, $groupMapper, $userPermissionMapper, $userMapper, null]);
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

    function it_compiles_roles_properly_1(User $admin)
    {
        $this->setUser($admin);
        $roles = $this->getRoles();
        $roles->shouldContain('admin');
        $roles->shouldContain('user');
    }

    function it_adds_roles(User $user)
    {
        $this->setUser($user);
        $this->hasRoleWithName('admin')->shouldBe(false);
        $this->addRoleByName('admin');
        $this->hasRoleWithName('admin')->shouldBe(true);
    }

    function it_compiles_roles_properly_2(User $user)
    {
        $this->setUser($user);
        $roles = $this->getRoles();
        $roles->shouldNotContain('admin');
    }

    function it_dies_when_you_add_roles_to_nobody()
    {
        $this->shouldThrow(UserRequiredException::class)->during('addRoleByName', ['admin']);
    }

    function it_bails_if_you_try_to_add_roles_already_added(User $admin, $roleMapper)
    {
        $this->setUser($admin);
        $roleMapper->getRoleWithName('admin')->shouldNotBeCalled();
        $this->addRoleByName('admin');
    }

    function it_bails_if_you_try_to_add_roles_that_dont_exist(User $admin)
    {
        $this->setUser($admin);
        $this->shouldThrow(InvalidRoleException::class)->during('addRoleByName', ['whatisthis']);
    }

    function it_performs_user_module_access(User $user)
    {
        $this->setUser($user);
        $this->canAccessController('Foo\Controller\ThisController')->shouldBe(true);
    }

    function it_performs_guest_module_access()
    {
        $this->canAccessController('Foo\Controller\ThisController')->shouldBe(false);
    }

    function it_rejects_module_access_with_insufficient_rights(User $user)
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

    function it_permits_relaxed_actions(User $user)
    {
        $this->setUser($user);
        $this->canAccessAction('Foo\Controller\AdminController', 'oddity')->shouldBe(true);
    }

    function it_accepts_authless_overrides()
    {
        $this->canAccessAction('Foo\Controller\AdminController', 'superodd')->shouldBe(true);
    }

    function it_reports_authless_overrides_as_not_needing_authentication()
    {
        $this->requiresAuthentication('Foo\Controller\AdminController', 'superodd')->shouldBe(false);
    }

    function it_throws_exceptions_for_authentication_checks_for_bad_config()
    {
        $this->shouldThrow(GuardExpectedException::class)->during('requiresAuthentication', ['foo', 'bar']);
    }

    function it_permits_rigorous_actions(User $admin)
    {
        $this->setUser($admin);
        $this->canAccessAction('Foo\Controller\ThisController', 'userList')->shouldBe(true);
    }

    function it_gates_rigorous_actions(User $user)
    {
        $this->setUser($user);
        $this->canAccessAction('Foo\Controller\ThisController', 'userList')->shouldBe(false);
    }

    function it_allows_action_overrides()
    {
        $this->canAccessAction('Foo\Controller\IndexController', 'home')->shouldBe(true);
        $this->canAccessAction('Foo\Controller\IndexController', 'login')->shouldBe(true);
    }

    function it_allows_resource_based_action_rules(User $user)
    {
        $this->setUser($user);
        $this->canAccessAction('Admin\Controller\ComplexController', 'save')->shouldBe(true);
    }

    /**
     * There is no resource rule defined for this action, so it should not be allowed.
     */
    function it_still_falls_back_onto_controller_definitions_when_actions_are_not_defined(User $user)
    {
        $this->setUser($user);
        $this->canAccessAction('Admin\Controller\ComplexController', 'load')->shouldBe(true);
    }

    /**
     * There is no resource rule defined for this action, so it should not be allowed.
     */
    function it_will_protect_in_cases_where_users_are_not_defined(User $user)
    {
        $this->canAccessAction('Admin\Controller\ComplexController', 'save')->shouldBe(false);
    }

    /**
     * There is no resource rule defined for this action, so it should not be allowed.
     */
    function it_supports_overriding_default_roles(User $user)
    {
        $this->canAccessAction('Admin\Controller\ComplexController', 'delete')->shouldBe(false);
    }


    function it_returns_roles_when_no_user_is_set()
    {
        $this->getRoles()->shouldHaveCount(0);
    }

    function it_returns_roles_when_users_are_set(User $admin)
    {
        $this->setUser($admin);
        $this->getRoles()->shouldBeArray();
        $this->getRoles()->shouldHaveCount(2);
    }

    function it_accepts_user_verbs(User $user)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'consume')->shouldBe(true);
    }

    function it_declines_user_verbs(User $user)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'pourout')->shouldBe(false);
    }

    function it_accepts_hierarchical_user_verbs(User $admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer', 'consume')->shouldBe(true);
    }

    function it_works_in_a_multiverb_situation_a(User $admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer', 'pour')->shouldBe(true);
    }

    function it_uses_user_exceptions(User $admin)
    {
        $this->setUser($admin);
        $this->isAllowed('beer', 'buy')->shouldBe(true);
    }

    function it_stops_userless_verbs()
    {
        $this->isAllowed('beer', 'consume')->shouldBe(false);
    }

    function it_declines_nonexistent_verbs(User $user)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'pourout')->shouldBe(false);
    }

    function it_defers_to_controllers_when_actions_are_not_configured(User $user)
    {
        $this->setUser($user);
        $this->canAccessAction('Foo\Controller\ThisController', 'notConfigured')->shouldBe(true);
    }

    function it_can_grant_users_access_to_strings(User $user, $userRules, $userRule2)
    {
        $this->setUser($user);
        $this->isAllowed('beer', 'buy')->shouldBe(false);
        $userRules->create($user, 'string', 'beer', ['buy'])->shouldBeCalled();
        $userRules->save($userRule2)->shouldBeCalled();
        $this->grantUserAccess('beer', 'buy');
    }

    function it_can_grant_users_access_to_existing_resources(User $user, ResourceInterface $resourceObject, UserPermissionInterface $userRule3)
    {
        $this->setUser($user);
        $this->isAllowed($resourceObject, 'foo')->shouldBe(false);
        $userRule3->addAction('foo')->shouldBeCalled();
        $this->grantUserAccess($resourceObject, 'foo');
    }

    function it_throws_exceptions_when_group_actions_are_requested_for_bad_resources()
    {
        $this->shouldThrow(UnknownResourceTypeException::class)->during('getGroupPermissions', [null]);
    }

    function it_throws_exceptions_when_no_user_is_set_and_user_actions_are_requested()
    {
        $this->shouldThrow(UserRequiredException::class)->during('getUserPermission', [null]);
    }

    function it_throws_exceptions_when_user_actions_are_requested_for_bad_resources(User $user)
    {
        $this->setUser($user);
        $this->shouldThrow(UnknownResourceTypeException::class)->during('getUserPermission', [null]);
    }

    function it_returns_allowed_rules_by_class(User $user, $groupActionRule)
    {
        // 1234, is the ID of the sole mocked object whose class is ResourceObject
        $this->setUser($user);
        $this->listAllowedByClass('ResourceObject')->shouldContain("1234");
    }

    function it_can_check_actions_by_resource_class(User $user)
    {
        $this->setUser($user);
        $this->isAllowedByResourceClass('ResourceObject', 'bar')->shouldBe(true);
    }

    function it_can_check_declined_actions_by_resource_class(User $user)
    {
        $this->setUser($user);
        $this->isAllowedByResourceClass('ResourceObject', 'fizzbuzz')->shouldBe(false);
    }

    /**
     * Give a role, access to a resource
     */
    function it_can_grant_access_to_roles_by_appending_actions(ResourceInterface $resourceObject, $groupRules, $groupActionRule)
    {
        $role = $this->getRoleWithName('user');
        $groupRules->update(Argument::any())->shouldBeCalled();
        $groupActionRule->addAction('foo')->shouldBeCalled();
        $this->grantRoleAccess($role, $resourceObject, 'foo');
    }

    function it_wont_grant_permissions_we_already_have(ResourceInterface $resourceObject, $groupRules)
    {
        $role = $this->getRoleWithName('user');
        $this->shouldThrow(ExistingAccessException::class)->during('grantRoleAccess', [$role, $resourceObject, 'bar']);
    }

    function it_can_report_that_permissions_are_required_1()
    {
        $this->requiresAuthentication('Foo\Controller\ThisController', 'foo')->shouldBe(true);
    }

    function it_can_report_that_permissions_are_required_2()
    {
        $this->requiresAuthentication('Foo\Controller\FreeForAll', 'get-name')->shouldBe(true);
    }

    function it_can_report_that_permissions_are_required_3()
    {
        $this->requiresAuthentication('Foo\Controller\FreeForAll', 'bar')->shouldBe(false);
    }

    function it_reports_that_it_has_users(User $user)
    {
        $this->setUser($user);
        $this->hasUser()->shouldBe(true);
    }

    function it_reports_that_it_has_no_users()
    {
        $this->hasUser()->shouldBe(false);
    }

    /**
     * Superadmin tests
     */
    function it_understands_super_admin_rights(
        RoleProviderInterface $roleMapper,
        GroupPermissionProviderInterface $groupRules,
        UserPermissionProviderInterface $userRules,
        UserMapper $userMapper,
        User $superAdmin,
        User $otherUser
    ) {
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
                        'actions' => [
                            'get-name' => ['user'],
                        ],
                    ],
                    'Foo\Controller\IndexController' => [
                        'default' => ['user'],
                        'actions' => [
                            'home' => [],
                            'login' => [],
                        ],
                    ],
                ],
            ],
        ];
        $this->beConstructedWith($config, $roleMapper, $groupRules, $userRules, $userMapper, $this->superAdminRole);

        $this->setUser($superAdmin);
        $this->isAllowed('beer', 'fizzbuzz')->shouldBe(true);

        $otherUser->getId()->willReturn(500);
        $otherUser->hasRole($this->superAdminRole)->willReturn(false);
        $this->setUser($otherUser);
        $this->isAllowed('beer', 'fizzbuzz')->shouldBe(false);
    }
}