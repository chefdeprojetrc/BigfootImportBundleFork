<?php

namespace Bigfoot\Bundle\ImportBundle\Entity;

/**
 * Class ImportedDataRepositoryInterface
 * @package Bigfoot\Bundle\ImportBundle\Entity
 */
interface ImportedDataRepositoryInterface
{
    /**
     * @param string $key
     * @param string $context
     * @return mixed
     */
    public function findImportedData($key, $context);
}
