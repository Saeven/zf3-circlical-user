<?php

namespace CirclicalUser\Mapper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine Mapper
 *
 * Provides common doctrine methods
 */
abstract class AbstractDoctrineMapper
{
    protected $entityManager;

    protected $entityName;

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
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

    public function save($e)
    {
        $this->getEntityManager()->persist($e);
        $this->getEntityManager()->flush($e);
    }

    public function update($e)
    {
        $this->getEntityManager()->merge($e);
        $this->getEntityManager()->flush();
    }

    public function delete($e)
    {
        $this->getEntityManager()->remove($e);
        $this->getEntityManager()->flush();
    }

    public function getPrototype()
    {
        return new $this->entityName;
    }
}
