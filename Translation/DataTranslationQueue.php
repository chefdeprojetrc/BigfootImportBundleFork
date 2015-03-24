<?php

namespace Bigfoot\Bundle\ImportBundle\Translation;

use Doctrine\Common\Persistence\Proxy;

/**
 * Class DataTranslationQueue
 * @package Bigfoot\Bundle\ImportBundle\Translation
 */
class DataTranslationQueue
{
    /** @var array */
    private $queue = array();

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
     * @param object $entity
     * @param string $property
     * @param string $locale
     * @param string $value
     * @return $this
     */
    public function add($entity, $property, $locale, $value)
    {
        $entityClass = $this->getClass($entity);
        if ($entityClass === null) {
            return null;
        }

        if (!isset($this->queue[$entityClass])) {
            $this->queue[$entityClass] = array();
        }

        if (!isset($this->queue[$entityClass][spl_object_hash($entity)])) {
            $this->queue[$entityClass][spl_object_hash($entity)] = array();
        }

        if (!isset($this->queue[$entityClass][spl_object_hash($entity)][$locale])) {
            $this->queue[$entityClass][spl_object_hash($entity)][$locale] = array();
        }

        $this->queue[$entityClass][spl_object_hash($entity)][$locale][$property] = array(
            'entity' => $entity,
            'content' => $value
        );

        return $this;
    }

    /**
     * @param object $entity
     * @param string $property
     * @param string $locale
     */
    public function remove($entity, $property = null, $locale = null)
    {
        $entityClass = $this->getClass($entity);
        if ($entityClass === null) {
            return null;
        }
        if (array_key_exists($entityClass, $this->queue) == false) {
            return null;
        }

        $hash = spl_object_hash($entity);
        if (array_key_exists($hash, $this->queue[$entityClass]) == false) {
            return null;
        }

        foreach ($this->queue[$entityClass][$hash] as $queueLocale => $properties) {
            if ($locale === null || $locale == $queueLocale) {
                if ($property === null) {
                    foreach (array_keys($properties) as $queueProperty) {
                        unset($this->queue[$entityClass][$hash][$queueLocale][$queueProperty]);
                    }
                } else {
                    unset($this->queue[$entityClass][$hash][$queueLocale][$property]);
                }
            }
        }
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->queue = array();

        return $this;
    }

    /**
     * @param object $entity
     * @return string
     */
    protected function getClass($entity)
    {
        if (is_object($entity)) {
            return ($entity instanceof Proxy) ? get_parent_class($entity) : get_class($entity);
        } else {
            return null;
        }
    }

}
