<?php

namespace Spec\CirclicalUser\Listener;

use CirclicalUser\Entity\UserPermission;
use CirclicalUser\Listener\UserEntityListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class UserEntityListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(UserEntityListener::DEFAULT_ENTITY);
        $this->shouldHaveType(UserEntityListener::class);
    }

    function it_returns_the_right_subscribed_events()
    {
        $this->beConstructedWith(UserEntityListener::DEFAULT_ENTITY);
        $this->getSubscribedEvents()->shouldContain('loadClassMetadata');
    }

    function it_processes_the_lcm_event(LoadClassMetadataEventArgs $eventArgs, ClassMetadata $classMetadata)
    {
        $this->beConstructedWith('Your\Custom\Entity');
        $classMetadata->getName()->willReturn(UserPermission::class);
        $classMetadata->getName()->shouldBeCalled();
        $eventArgs->getClassMetadata()->willReturn($classMetadata);
        $this->loadClassMetadata($eventArgs);
    }

    function it_skips_the_lcm_event_on_default_entity(LoadClassMetadataEventArgs $eventArgs, ClassMetadata $classMetadata)
    {
        $this->beConstructedWith(UserEntityListener::DEFAULT_ENTITY);
        $eventArgs->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->shouldNotBeCalled();
        $this->loadClassMetadata($eventArgs);
    }
}
