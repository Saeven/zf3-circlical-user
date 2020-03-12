<?php


namespace CirclicalUser\Controller;

use CirclicalUser\Entity\TemporaryResource;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\UserProviderInterface;
use CirclicalUser\Service\AccessService;
use Laminas\Console\Exception\RuntimeException;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Mvc\Controller\AbstractActionController;

class CliController extends AbstractActionController
{
    private $userMapper;

    private $groupPermissionMapper;

    private $userPermissionMapper;

    private $accessService;

    private $roleProvider;

    public function __construct(UserProviderInterface $userMapper, RoleProviderInterface $roleProvider,
                                GroupPermissionProviderInterface $groupPermissionMapper, UserPermissionProviderInterface $userPermissionMapper,
                                AccessService $accessService)
    {
        $this->roleProvider = $roleProvider;
        $this->userMapper = $userMapper;
        $this->groupPermissionMapper = $groupPermissionMapper;
        $this->userPermissionMapper = $userPermissionMapper;
        $this->accessService = $accessService;
    }

    public function grantResourceRoleAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException("You can only do this from the console!");
        }

        $params = $this->params();
        $role = $this->roleProvider->getRoleWithName($params->fromRoute('roleName'));

        if (!$role) {
            throw new RuntimeException("That role couldn't be found");
        }

        if (!class_exists($params->fromRoute('resourceClass'))) {
            throw new RuntimeException("The class {$params->fromRoute('resourceClass')} couldn't be found. Did you escape your backslashes?");
        }

        $resource = new TemporaryResource($params->fromRoute('resourceClass'), $params->fromRoute('resourceId'));
        $this->accessService->grantRoleAccess($role, $resource, $params->fromRoute('verb'));
    }

    public function grantResourceUserAction()
    {
        $request = $this->getRequest();

        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException("You can only do this from the console!");
        }

        $params = $this->params();
        $user = $this->userMapper->findByEmail($this->params('userEmail'));

        if (!$user) {
            throw new RuntimeException("That user couldn't be found");
        }

        if (!class_exists($params->fromRoute('resourceClass'))) {
            throw new RuntimeException("The class {$params->fromRoute('resourceClass')} couldn't be found. Did you escape your backslashes?");
        }

        $resource = new TemporaryResource($params->fromRoute('resourceClass'), $params->fromRoute('resourceId'));
        $this->accessService->setUser($user);
        $this->accessService->grantUserAccess($resource, $params->fromRoute('verb'));
    }
}