<?php

namespace CirclicalUser\Controller\Plugin;

use CirclicalUser\Entity\User;
use CirclicalUser\Service\AuthenticationService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class AuthenticationPlugin extends AbstractPlugin
{

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;


    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Pass me an email/username combo and I'll start the user session
     * @param $email
     * @param $pass
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
     * @param User $user
     * @param $password
     */
    public function create(User $user, $password)
    {
        $this->authenticationService->create($user, $password);
    }

}