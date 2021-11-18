<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;

/**
 * Doctrine Mapper
 *
 * Provides common doctrine methods
 */
abstract class AbstractDoctrineMapper
{
    protected ?EntityManager $entityManager;

    protected string $entityName;

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getDatabase(): Connection
    {
        return $this->entityManager->getConnection();
    }

    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository($this->entityName);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function save(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);
    }


    // ToDo: this function can be deleted, because merge is deprecated, right?
    public function update(object $entity): void
    {
        $this->getEntityManager()->merge($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function getPrototype(): object
    {
        return new $this->entityName();
    }
}
