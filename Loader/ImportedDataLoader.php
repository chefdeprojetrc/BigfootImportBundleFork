<?php

namespace Bigfoot\Bundle\ImportBundle\Loader;

use Symfony\Component\Validator\Validator;

/**
 * Class ImportedDataLoader
 */
class ImportedDataLoader
{
    /** @var int */
    protected $batchSize = 50;

    /** @var int */
    protected $iteration = 0;

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var \Symfony\Component\Validator\Validator */
    protected $validator;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Component\Validator\Validator $validator
     */
    public function __construct($entityManager, $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
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
        if ($this->iteration++ % 50) {
            $this->flush();
        }
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
}
