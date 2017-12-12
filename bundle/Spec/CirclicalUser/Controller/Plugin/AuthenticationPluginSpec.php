<?php

namespace Spec\CirclicalUser\Controller\Plugin;

use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthenticationPluginSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Controller\Plugin\AuthenticationPlugin');
    }

    public function let(AuthenticationService $authenticationService, AccessService $accessService, User $user, User $newUser)
    {
        $authenticationService->authenticate(Argument::any(), Argument::any())->willReturn(null);
        $authenticationService->authenticate('user', 'pass')->willReturn($user);
        $authenticationService->getIdentity()->willReturn($user);

        $authenticationService->create(Argument::type(User::class), Argument::any(), Argument::any())->willReturn($newUser);

        $this->beConstructedWith($authenticationService, $accessService);
    }

    public function it_can_authenticate_users($authenticationService, $user)
    {
        $authenticationService->authenticate(Argument::any(), Argument::any())->shouldBeCalled();
        $this->authenticate('user', 'pass')->shouldBeLike($user);
    }

    public function it_can_return_identity($authenticationService, $user)
    {
        $authenticationService->getIdentity()->shouldBeCalled();
        $this->getIdentity()->shouldBeLike($user);
    }

    public function it_clears_identities($authenticationService)
    {
        $authenticationService->clearIdentity()->shouldBeCalled();
        $this->clearIdentity();
    }

    public function it_creates_users($authenticationService, $newUser)
    {
        $authenticationService->create($newUser, 'userA', '123')->shouldBeCalled();
        $this->create($newUser, 'userA', '123');
    }

    public function it_fails_on_require_when_there_is_no_auth(AuthenticationService $authenticationService)
    {
        $authenticationService->getIdentity()->willReturn(null);
        $this->shouldThrow(UserRequiredException::class)->during('requireIdentity');
    }

    public function it_succeeds_on_require_when_there_is_auth(AuthenticationService $authenticationService, User $user)
    {
        $authenticationService->getIdentity()->willReturn($user);
        $this->requireIdentity()->shouldBe($user);
    }
}
