<?php

namespace Bigfoot\Bundle\ImportBundle\Factory;

use Bigfoot\Bundle\ImportBundle\Mapper\AbstractDataMapper;

/**
 * Class MapperFactory
 * @package Bigfoot\Bundle\ImportBundle\Factory
 */
class MapperFactory
{
    /** @var array */
    private $mappers = array();

    /**
     * @param AbstractDataMapper $mapper
     * @throws \Exception
     */
    public function addMapper($mapper)
    {
        if (!($mapper instanceof AbstractDataMapper)) {
            throw new \Exception('Services tagged bigfoot.import.mapper should extend BigfootImportBundle:Mapper/AbstractDataMapper.');
        }

        $this->mappers[$mapper->getName()] = $mapper;
    }

    /**
     * @param $name
     * @return AbstractDataMapper|null
     */
    public function getMapper($name)
    {
        return isset($this->mappers[$name]) ? $this->mappers[$name] : null;
    }
}
