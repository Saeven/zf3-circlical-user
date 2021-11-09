<?php

declare(strict_types=1);

namespace CirclicalUser\Controller\Plugin;

use CirclicalUser\Exception\BadPasswordException;
use CirclicalUser\Exception\NoSuchUserException;
use CirclicalUser\Exception\PersistedUserRequiredException;
use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserInterface;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class AuthenticationPlugin extends AbstractPlugin
{
    private AuthenticationService $authenticationService;

    private AccessService $accessService;

    public function __construct(AuthenticationService $authenticationService, AccessService $accessService)
    {
        $this->authenticationService = $authenticationService;
        $this->accessService = $accessService;
    }

    /**
     * Pass me an email/username combo and I'll start the user session
     *
     * @throws BadPasswordException
     * @throws NoSuchUserException
     */
    public function authenticate(string $email, string $pass): UserInterface
    {
        return $this->authenticationService->authenticate($email, $pass);
    }

    public function getIdentity(): ?UserInterface
    {
        return $this->authenticationService->getIdentity();
    }

    /**
     * Get a user identity, or else!
     *
     * @throws UserRequiredException
     */
    public function requireIdentity(): UserInterface
    {
        $user = $this->authenticationService->getIdentity();
        if ($user === null) {
            throw new UserRequiredException();
        }

        return $user;
    }

    /**
     * Clear identity and reset tokens
     */
    public function clearIdentity(): void
    {
        $this->authenticationService->clearIdentity();
    }

    /**
     * Give me a user, username and password; and I'll create authentication records for you
     *
     * @param string $username Can be an email address or username, should be validated prior
     * @throws PersistedUserRequiredException
     */
    public function create(UserInterface $user, string $username, string $password): AuthenticationRecordInterface
    {
        return $this->authenticationService->create($user, $username, $password);
    }

    /**
     * @param ResourceInterface|string $resource
     */
    public function isAllowed($resource, string $action): bool
    {
        return $this->accessService->isAllowed($resource, $action);
    }
}
