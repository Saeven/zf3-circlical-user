<?php

namespace CirclicalUser\Controller\Plugin;

use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class AuthenticationPlugin extends AbstractPlugin
{

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AccessService
     */
    private $accessService;


    public function __construct(AuthenticationService $authenticationService, AccessService $accessService)
    {
        $this->authenticationService = $authenticationService;
        $this->accessService = $accessService;
    }

    /**
     * Pass me an email/username combo and I'll start the user session
     *
     * @param $email
     * @param $pass
     *
     * @return User
     * @throws \CirclicalUser\Exception\BadPasswordException
     * @throws \CirclicalUser\Exception\NoSuchUserException
     */
    public function authenticate($email, $pass)
    {
        return $this->authenticationService->authenticate($email, $pass);
    }

    public function getIdentity()
    {
        return $this->authenticationService->getIdentity();
    }

    /**
     * Clear identity and reset tokens
     */
    public function clearIdentity()
    {
        $this->authenticationService->clearIdentity();
    }

    /**
     * Give me a user and password, and I'll create authentication records for you
     *
     * @param User   $user
     * @param string $username Can be an email address or username, should be validated prior
     * @param string $password
     * @param bool   $autoLogin Provides the option to disable auto login after user is created
     */
    public function create(User $user, $username, $password, $autoLogin = true)
    {
        $this->authenticationService->create($user, $username, $password, $autoLogin);
    }

    public function isAllowed($resource, $action)
    {
        return $this->accessService->isAllowed($resource, $action);
    }

}
