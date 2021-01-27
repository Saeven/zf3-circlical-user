<?php

namespace Spec\CirclicalUser\Factory;

use CirclicalUser\Mapper\RoleMapper;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Zend\ServiceManager\ServiceManager;

class AbstractDoctrineMapperFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Factory\AbstractDoctrineMapperFactory');
    }

    public function it_is_not_invoked_unnnecessarily(ServiceManager $serviceManager)
    {
        $this->canCreate($serviceManager, 'Foo\Mapper\SomethingMapper')->shouldBe(true);
        $this->canCreate($serviceManager, 'Foo\Controller\IndexController')->shouldBe(false);
    }

    public function it_creates_its_service(ServiceManager $serviceManager, EntityManager $entityManager)
    {
        $serviceManager->get('doctrine.entitymanager.orm_default')->willReturn($entityManager);
        $this->__invoke($serviceManager, RoleMapper::class)->shouldBeAnInstanceOf(RoleMapper::class);
    }
}
