<?php

namespace Bigfoot\Bundle\ImportBundle\Mapper;

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

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Bigfoot\Bundle\ImportBundle\Translation\DataTranslationQueue $translationQueue
     */
    public function __construct($entityManager, $translationQueue)
    {
        $this->entityManager    = $entityManager;
        $this->translationQueue = $translationQueue;
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
}
