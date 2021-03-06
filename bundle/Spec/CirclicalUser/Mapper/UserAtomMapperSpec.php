<?php

namespace Spec\CirclicalUser\Mapper;

use CirclicalUser\Entity\UserAtom;
use CirclicalUser\Mapper\UserAtomMapper;
use CirclicalUser\Provider\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserAtomMapperSpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, EntityRepository $entityRepository, Connection $connection, Statement $statement)
    {
        $this->setEntityManager($entityManager);
        $entityManager->getRepository(UserAtom::class)->willReturn($entityRepository);
        $entityManager->getConnection()->willReturn($connection);
        $connection->prepare(Argument::any())->willReturn($statement);
        $statement->bindParam(Argument::any(), Argument::any())->willReturn(true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserAtomMapper::class);
    }

    function it_can_get_atoms_by_key(UserInterface $user, EntityRepository $entityRepository, EntityManager $entityManager, UserAtom $atom)
    {
        $user->getId()->willReturn(1);
        $key = 'test';
        $entityRepository->findOneBy([
            'user' => $user,
            'key' => $key,
        ])->willReturn($atom);

        $entityManager->detach($atom)->shouldBeCalled();
        $this->getAtom($user, $key);
    }

    function it_can_get_atoms_by_key_and_not_detach_them_from_the_entity_manager(UserInterface $user, EntityRepository $entityRepository, EntityManager $entityManager, UserAtom $atom)
    {
        $user->getId()->willReturn(1);
        $key = 'test';
        $entityRepository->findOneBy([
            'user' => $user,
            'key' => $key,
        ])->willReturn($atom);

        $entityManager->detach($atom)->shouldNotBeCalled();
        $this->getAtom($user, $key, false);
    }

    function it_can_search_atoms(EntityRepository $entityRepository)
    {
        $entityRepository->findBy(['key' => 'abc', 'value' => '123'])->willReturn([]);
        $this->search('abc', '123');
    }
}
