<?php

namespace Spec\CirclicalUser\Entity;

use CirclicalUser\Entity\UserApiToken;
use CirclicalUser\Provider\UserInterface;
use PhpSpec\ObjectBehavior;

class UserApiTokenSpec extends ObjectBehavior
{
    public function let(UserInterface $user)
    {
        $this->beConstructedWith($user, 2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserApiToken::class);
    }

    function it_understands_scope()
    {
        $this->hasScope(2)->shouldBe(true);
        $this->addScope(1);
        $this->hasScope(2)->shouldBe(true);
        $this->hasScope(1)->shouldBe(true);
        $this->hasScope(3)->shouldBe(true);
        $this->hasScope(4)->shouldBe(false);
        $this->clearScope();
        $this->hasScope(1)->shouldBe(false);

    }

    function it_can_count_tags()
    {
        $this->getTimesUsed()->shouldBe(0);
        $this->getLastUsed()->shouldBe(null);
        $this->tagUse();
        $this->getTimesUsed()->shouldBe(1);
        $this->getLastUsed()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    function it_has_a_convenience_method_for_tokens()
    {
        $this->getUuid()->toString()->shouldBeLike($this->getToken());
    }
}
