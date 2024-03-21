<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use RuntimeException;

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
        if (!$this->entityManager) {
            throw new RuntimeException('No entity manager was set');
        }

        return $this->entityManager;
    }

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getDatabase(): Connection
    {
        if (!$this->entityManager) {
            throw new RuntimeException('No entity manager was set');
        }

        return $this->entityManager->getConnection();
    }

    public function getRepository(): EntityRepository
    {
        if (!$this->entityManager) {
            throw new RuntimeException('No entity manager was set');
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        return $this->entityManager->getRepository($this->entityName);
    }

    public function save(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);
    }

    /**
     * @deprecated Please use save instead
     */
    public function update(object $entity): void
    {
        $this->save($entity);
    }

    public function delete(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function getPrototype(): object
    {
        /** @psalm-suppress InvalidStringClass */
        return new $this->entityName();
    }
}
