<?php

namespace Bigfoot\Bundle\ImportBundle\Mapper;

use Bigfoot\Bundle\ImportBundle\TransversalData\TransversalDataQueue;
use Gedmo\Translatable\Entity\Translation;

/**
 * Class AbstractDataMapper
 * @package Bigfoot\Bundle\ImportBundle\Mapper
 */
abstract class AbstractDataMapper
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var \Bigfoot\Bundle\ImportBundle\Translation\DataTranslationQueue */
    protected $translationQueue;

    /** @var  TransversalDataQueue */
    protected $transversalDataQueue;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Bigfoot\Bundle\ImportBundle\Translation\DataTranslationQueue $translationQueue
     * @param TransversalDataQueue $transversalDataQueue
     */
    public function __construct($entityManager, $translationQueue, $transversalDataQueue)
    {
        $this->entityManager        = $entityManager;
        $this->translationQueue     = $translationQueue;
        $this->transversalDataQueue = $transversalDataQueue;
    }

    public function addTransversalData($key, $entities)
    {
        $this->transversalDataQueue->add($key, $entities);
    }

    /**
     * @param object $entity
     * @param string $property
     * @param array $values
     */
    protected function translateProperty($entity, $property, $values)
    {
        foreach ($values as $locale => $value) {
            $this->translationQueue->add($entity, $property, $locale, $value);
        }
    }

    /**
     * @param object $source
     * @param object $destination
     * @return mixed
     */
    abstract public function map($source, $destination);

    /**
     * @param object $destination
     */
    public function unmap($destination)
    {
        $this->translationQueue->remove($destination);
    }

    /**
     * @return string
     */
    abstract public function getName();
}
