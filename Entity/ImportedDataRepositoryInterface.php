<?php

namespace Bigfoot\Bundle\ImportBundle\Entity;

/**
 * Class ImportedDataRepositoryInterface
 * @package Bigfoot\Bundle\ImportBundle\Entity
 */
interface ImportedDataRepositoryInterface
{
    /**
     * @return string
     */
    public function getImportedIdentifier();
}
