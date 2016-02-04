<?php

namespace Bigfoot\Bundle\ImportBundle\TransversalData;

use Bigfoot\Bundle\CoreBundle\Exception\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class TransversalDataQueue
{
    /** @var  EntityManager */
    private $entityManager;

    /** @var  PropertyAccessor */
    private $propertyAccessor;

    /**
     * @param EntityManager $entityManager
     * @param PropertyAccessor $propertyAccessor
     */
    public function __construct(EntityManager $entityManager, PropertyAccessor $propertyAccessor)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /** @var array */
    private $queue = array();

    /**
     * @param string $key
     * @param array|ArrayCollection $entities
     */
    public function add($key, $entities)
    {
        if (!is_array($entities) && !($entities instanceof ArrayCollection)) {
            throw new InvalidArgumentException('Entities must be an array or an ArrayCollection !');
        }

        if (array_key_exists($key, $this->queue)) {
            throw new InvalidArgumentException(sprintf('Key "%s" already exists in TransversalDataQueue', $key));
        }

        $this->queue[$key] = $entities;
    }

    /**
     * @param string $key
     */
    public function rebuildReferences($key = null)
    {
        if (null !== $key) {
            if (!array_key_exists($key, $this->queue)) {
                throw new InvalidArgumentException(sprintf('Key "%s" does not exist in TransversalDataQueue', $key));
            }

            $this->rebuildReferencesForEntity($key);
        } else {
            foreach (array_keys($this->queue) as $key) {
                $this->rebuildReferencesForEntity($key);
            }
        }
    }

    /**
     * @param string $key
     */
    private function rebuildReferencesForEntity($key)
    {
        $entities = $this->queue[$key];
        $mergedEntities = array();

        foreach ($entities as $entity) {
            $mergedEntities[] = $this->entityManager->merge($entity);
        }
        $this->queue[$key] = $mergedEntities;
        unset($entities);
    }

    public function getTransversalData($key, $identifier, $needle)
    {
        if (!array_key_exists($key, $this->queue)) {
            throw new InvalidArgumentException(sprintf('Key "%s" does not exist in TransversalDataQueue', $key));
        }

        foreach ($this->queue[$key] as $entity) {
            if ($needle == $this->propertyAccessor->getValue($entity, $identifier)) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param array $queue
     * @return $this
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->queue = array();

        return $this;
    }
}
