<?php

namespace Bigfoot\Bundle\ImportBundle\Manager;

use Bigfoot\Bundle\ImportBundle\Entity\ImportedDataRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator;

/**
 * Class ImportedDataManager
 */
class ImportedDataManager
{
    /** @var int */
    protected $batchSize = 50;

    /** @var int */
    protected $iteration = 0;

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var \Symfony\Component\Validator\Validator */
    protected $validator;

    /** @var \Symfony\Component\PropertyAccess\PropertyAccessor */
    protected $propertyAccessor;

    /** @var array */
    protected $importedEntities = array();

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Component\Validator\Validator $validator
     * @param \Symfony\Component\PropertyAccess\PropertyAccessor $propertyAccessor
     */
    public function __construct($entityManager, $validator, $propertyAccessor)
    {
        $this->entityManager    = $entityManager;
        $this->validator        = $validator;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param int $batchSize
     * @return $this
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function load($entity)
    {
        if (!$this->validator->validate($entity)) {
            return false;
        }

        $propertyAccessor = $this->propertyAccessor;
        $entityClass      = get_class($entity);
        $property         = $this->getImportedIdentifier($entityClass);
        $importedId       = $propertyAccessor->getValue($entity, $property);

        if (!isset($this->importedEntities[$entityClass])) {
            $this->importedEntities[$entityClass] = array();
        }

        $this->importedEntities[$entityClass][] = $importedId;

        $em = $this->entityManager;
        $em->persist($entity);

        $this->batch();

        return true;
    }

    /**
     *
     */
    public function batch()
    {
        if ($this->iteration++ % $this->batchSize) {
            $this->flush();
        }
    }

    public function terminate()
    {
        $this->flush();
        $this->iteration = 0;
        $this->importedEntities = array();
    }

    /**
     *
     */
    public function flush()
    {
        $em = $this->entityManager;
        $em->flush();
        $em->clear();

        gc_collect_cycles();
    }

    /**
     * @param string $class
     * @param string $key
     * @param string $context
     * @return mixed
     * @throws \Exception
     */
    public function findExistingEntity($class, $key, $context = null)
    {
        $property = $this->getImportedIdentifier($class);

        /** @var ImportedDataRepositoryInterface $repo */
        $repo   = $this->entityManager->getRepository($class);
        $entity = $repo->findOneBy(array($property => $key));

        if (!$entity && isset($this->importedEntities[$class]) && in_array($this->importedEntities[$class][$key], $this->importedEntities)) {
            $entity = $this->importedEntities;
        }

        if (!$entity) {
            $entity = new $class();
        }

        return $entity;
    }

    protected function getImportedIdentifier($class)
    {
        /** @var ImportedDataRepositoryInterface $repo */
        $repo = $this->entityManager->getRepository($class);

        if (!($repo instanceof ImportedDataRepositoryInterface)) {
            throw new \Exception('Imported entities managed by this manager must have a repository implementing the Bigfoot\\Bundle\\ImportBundle\\Entity\\ImportedDataRepositoryInterface');
        }

        return $repo->getImportedIdentifier();
    }

    protected function getImportedId($entity)
    {
        $propertyAccessor = $this->propertyAccessor;
        $entityClass      = get_class($entity);
        $property         = $this->getImportedIdentifier($entityClass);

        return $propertyAccessor->getValue($entity, $property);
    }
}
