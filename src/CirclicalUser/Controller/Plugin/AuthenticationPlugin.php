<?php

namespace CirclicalUser\Controller\Plugin;

use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

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
    public function authenticate(string $email, string $pass)
    {
        return $this->authenticationService->authenticate($email, $pass);
    }

    public function getIdentity()
    {
        return $this->authenticationService->getIdentity();
    }

    /**
     * Get a user identity, or else!
     * @return User|null
     * @throws UserRequiredException
     */
    public function requireIdentity()
    {
        $user = $this->authenticationService->getIdentity();
        if (!$user) {
            throw new UserRequiredException();
        }

        return $user;
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
     */
    public function create(User $user, $username, $password)
    {
        $this->authenticationService->create($user, $username, $password);
    }

    public function isAllowed($resource, $action)
    {
        return $this->accessService->isAllowed($resource, $action);
    }

}