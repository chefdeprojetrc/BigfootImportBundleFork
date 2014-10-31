<?php

namespace Bigfoot\Bundle\ImportBundle\Manager;

use Bigfoot\Bundle\ImportBundle\Entity\ImportedDataRepositoryInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class ImportedDataManager
 */
class ImportedDataManager
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
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
        /** @var ImportedDataRepositoryInterface $repo */
        $repo = $this->entityManager->getRepository($class);

        if (!($repo instanceof ImportedDataRepositoryInterface)) {
            throw new \Exception('Imported entities managed by this manager must have a repository implementing the Bigfoot\\Bundle\\ImportBundle\\Entity\\ImportedDataRepositoryInterface');
        }

        return $repo->findImportedData($key, $context);
    }
}
