<?php

namespace Bigfoot\Bundle\ImportBundle\Mapper;

use Bigfoot\Bundle\ImportBundle\Manager\TransversalDataManager;
use Bigfoot\Bundle\ImportBundle\Translation\DataTranslationQueue;
use Gedmo\Translatable\Entity\Translation;
use \Doctrine\ORM\EntityManagerInterface;

/**
 * Class AbstractDataMapper
 * @package Bigfoot\Bundle\ImportBundle\Mapper
 */
abstract class AbstractDataMapper
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var DataTranslationQueue */
    protected $translationQueue;

    /** @var TransversalDataManager */
    protected $transversalDataManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param DataTranslationQueue $translationQueue
     * @param TransversalDataManager $transversalDataManager
     */
    public function __construct(EntityManagerInterface $entityManager, DataTranslationQueue $translationQueue, TransversalDataManager $transversalDataManager)
    {
        $this->entityManager          = $entityManager;
        $this->translationQueue       = $translationQueue;
        $this->transversalDataManager = $transversalDataManager;
    }

    public function addTransversalData($key, $entities)
    {
        $this->transversalDataManager->add($key, $entities);
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
