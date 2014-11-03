<?php

namespace Bigfoot\Bundle\ImportBundle\Mapper;

/**
 * Class AbstractDataMapper
 * @package Bigfoot\Bundle\ImportBundle\Mapper
 */
abstract class AbstractDataMapper
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var \Doctrine\Common\Annotations\FileCacheReader */
    protected $annotationReader;

    /** @var \Bigfoot\Bundle\CoreBundle\Entity\TranslationRepository */
    protected $bigfootTransRepo;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Doctrine\Common\Annotations\FileCacheReader $annotationReader
     * @param \Bigfoot\Bundle\CoreBundle\Entity\TranslationRepository $bigfootTransRepo
     */
    public function __construct($entityManager, $annotationReader, $bigfootTransRepo)
    {
        $this->entityManager    = $entityManager;
        $this->annotationReader = $annotationReader;
        $this->bigfootTransRepo = $bigfootTransRepo;
    }

    /**
     * @param object $entity
     * @param string $property
     * @param array $values
     */
    protected function translateProperty($entity, $property, $values)
    {
        $em               = $this->entityManager;
        $bigfootTransRepo = $this->bigfootTransRepo;
        $reflectionClass  = new \ReflectionClass($entity);
        $gedmoAnnotations = $this->annotationReader->getClassAnnotation($reflectionClass, 'Gedmo\\Mapping\\Annotation\\TranslationEntity');

        if ($gedmoAnnotations !== null && $gedmoAnnotations->class != '') {
            $translationRepository = $bigfootTransRepo;
        } else {
            $translationRepository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        }

        foreach ($values as $locale => $value) {
            $translationRepository->translate($entity, $property, $locale, $value);
        }
    }

    /**
     * @param object $source
     * @param object $destination
     * @return mixed
     */
    abstract public function map($source, $destination);
}
