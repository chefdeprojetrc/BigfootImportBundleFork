<?php

namespace Bigfoot\Bundle\ImportBundle\Services\DataMapper;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Yaml\Yaml;

/**
 * Class XmlMapper
 *
 * Implements import XMl data in project database
 *
 * @package Bigfoot\Bundle\ImportBundle\Services\DataMapper
 */
class XmlMapper
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var array */
    private $mappingInfo;

    /**
     * Construct an XmlParser
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $mappingFile
     * @return $this
     */
    public function setMappingInfo($mappingFile)
    {
        $this->mappingInfo = Yaml::parse(file_get_contents($mappingFile));

        return $this;
    }

    /**
     * Start mapping
     *
     * @param \SimpleXMLElement $xmlElement XmlElement to import
     *
     * @return mixed Return null if mappingInfo isn't valid otherwise return an object
     */
    public function map(\SimpleXMLElement $xmlElement)
    {
        if ($this->mappingInfo == null || !is_array($this->mappingInfo)) {
            throw new \Exception('Import failed: configuration file given couldn\'t be resolved to a valid configuration');
        } else {
            return $this->xmlElementToEntity($this->mappingInfo, $xmlElement);
        }
    }

    /**
     * Internal recursive function to perform mapping.
     * Perform mapping for parent entity, and launch recursively for all children entity
     *
     * @param array             $mapping
     * @param \SimpleXmlElement $xmlElement
     * @param $parentElement
     *
     * @return mixed
     */
    private function xmlElementToEntity(array $mapping, \SimpleXMLElement $xmlElement, $parentElement = null)
    {
        $em = $this->em;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        /** @var TranslationRepository $repository */
        $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $description = $mapping[key($mapping)];

        if (is_array($description)) {
            $object = null;
            if (isset($description['key'])) {
                // Multiple key
                if (is_array($description['key']['xmlKey'])) {
                    $keys = array();
                    foreach($description['key']['xmlKey'] as $id => $key) {
                        $tmpKey = $xmlElement->xpath($key);
                        $keys[$description['key']['keyName'][$id]] = $tmpKey[0];
                    }
                } else {
                    // We attempt to retrieve an existing object corresponding to XML data
                    if ((string)$description['key']['xmlKey'] == '@uniqueId') {
                        $uniqueId = $xmlElement->xpath('@id');
                        $nodeParent = $xmlElement->xpath('../..');
                        $keys = array(0 => $nodeParent[0]->getName().'-'.(string)$uniqueId[0]);
                    }
                    else {
                        $keys = $xmlElement->xpath($description['key']['xmlKey']);
                    }
                }

                // If there is a findFunction, use it instead of findOneBy
                if(isset($description['key']['findFunction'])) {
                    $findFunction = $description['key']['findFunction'];
                } else {
                    $findFunction = 'findOneBy';
                }

                // If xmlKey not found in XMl Data, there's no object to create. So we return null
                if (!isset($keys[0])) {
                    return null;
                } elseif(is_array($description['key']['xmlKey'])) {
                    $object = $em->getRepository($description['repository'])->$findFunction($keys);
                } else{
                    $object = $em->getRepository($description['repository'])->$findFunction(array($description['key']['keyName'] => $keys[0]));
                }

            }
            if (isset($description['oneToOne'])) {
                $object = $em->getRepository($description['repository'])->findOneBy(array($description['oneToOne']['columnName'] => $parentElement));
            }

            if(null == $object){
                if(!array_key_exists('nullable',$description) || $description['nullable'] == false) {
                    $object = new $description['class']();
                }
                // If parameter nullable set to true, we do not create a new object if we can't find one
                else {
                    return false;
                }
            }


            foreach ($description['mapping'] as $function => $xpath) {
                if (is_array($xpath)) {
                    if (isset($xpath['languages'])) {
                        foreach ($xpath['languages'] as $language => $element) {
                            $translation = $xmlElement->xpath($element);
                            if (count($translation) > 0) {
                                $repository->translate($object, $function, $language, $translation[0]);
                            }
                        }
                    } else {
                        foreach ($xpath as $type => $typeDescription) {
                            // Nettoyage avant import
                            if (isset($typeDescription['clear'])) {
                                if (is_array($typeDescription['clear'])) {
                                    $oldElements = $object->{$typeDescription['clear']['getFunction']}();
                                    if ($oldElements != null) {
                                        foreach ($oldElements as $oldElement) {
                                            $object->{$typeDescription['clear']['removeFunction']}($oldElement);
                                            if(isset($typeDescription['clear']['removeChild']) && $typeDescription['clear']['removeChild'])
                                            {
                                                $em->remove($oldElement);
                                            }
                                        }
                                    }
                                }
                            }

                            if (count($xmlElement->xpath($typeDescription['xpath'])) == 0) {
                                //$object->$function(null);
                            } else {
                                foreach ($xmlElement->xpath($typeDescription['xpath']) as $childElement) {
                                    $myChild = $this->xmlElementToEntity(array($type => $typeDescription), $childElement, $object);
                                    if ($myChild != null) {
                                        $object->$function($myChild);
                                    }
                                }
                            }
                        }
                    }
                } else {

                    if ((string)$xpath == '@uniqueId') {
                        $uniqueId = $xmlElement->xpath('@id');
                        $nodeParent = $xmlElement->xpath('../..');
                        $object->$function((string)$nodeParent[0]->getName());
                        $object->setUniqueId($nodeParent[0]->getName().'-'.(string)$uniqueId[0]);
                    }
                    else {
                        if ( count($xmlElement->xpath($xpath)) == 0) {
                            $object->$function(null);
                        } else {
                            $path = $xmlElement->xpath($xpath);
                            $object->$function((string) $path[0]);
                        }
                    }
                }
            }

            if (isset($description['relationToParent']) && is_string($description['relationToParent'])) {
                $object->{$description['relationToParent']}($parentElement);
            }

            $em->persist($object);
            $em->flush();
            $em->refresh($object);

            return $object;
        } else {
            return null;
        }

    }
}
