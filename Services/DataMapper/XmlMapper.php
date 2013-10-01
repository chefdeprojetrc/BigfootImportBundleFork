<?php
/**
 * Created by JetBrains PhpStorm.
 * User: splancon
 * Date: 01/10/13
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

namespace Bigfoot\Bundle\ImportBundle\Services\DataMapper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Yaml\Yaml;

class XmlMapper {

    private $controller;
    private $mapping_info;

    public function __construct(Controller $controller, $mappingFile)
    {
        $this->controller = $controller;

        // Chargement de la configuration du mapping
        $this->mapping_info = Yaml::parse(file_get_contents($mappingFile));

        if ($this->mapping_info == null || !is_array($this->mapping_info)) {
            echo "Mapping schema isn't valid.\nImport will not be possible without a valid mapping schema.\n";
        }
    }

    public function map($xmlElement) {

        if ($this->mapping_info == null || !is_array($this->mapping_info)) {
            echo "Mapping schema isn't valid.\nPlease load a valid mapping schema before performing an import.\n";
        } else {
            $this->xmlElementToEntity($this->mapping_info, $xmlElement);
        }
    }

    private function xmlElementToEntity(array $mapping_info, $xmlElement, $parentElement = null)
    {
        $em = $this->controller->getDoctrine()->getManager();

        foreach ($mapping_info as $objectName => $description) {
            if (is_array($description)) {

                $oldObject = null;
                if (isset($description['key'])) {
                    // Tentative de récupération de l'objet
                    $oldObject = $em->getRepository($description['repository'])->findOneBy(array($description['key']['keyName'] => $xmlElement->xpath($description['key']['xmlKey'])[0]));
                }

                if (!is_null($oldObject)) {
                    $$objectName = $oldObject;
                } else {
                    $$objectName = new $description['class']();
                }

                foreach ($description['mapping'] as $function => $xpath) {
                    if (is_array($xpath)) {
                        if (isset($xpath['languages'])) {
                            $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
                            foreach ($xpath['languages'] as $language => $element) {
                                $translation = $xmlElement->xpath($element);
                                if (count($translation) > 0) {
                                    $repository->translate($$objectName, $function, $language, $translation[0]);
                                }
                            }
                        } else {
                            foreach ($xpath as $type => $type_description) {
                                // Nettoyage avant import
                                if (isset($type_description['clear'])) {
                                    if (is_array($type_description['clear']) )
                                    {
                                        $oldElements = $$objectName->$type_description['clear']['getFunction']();
                                        if ($oldElements != null) {
                                            foreach ($oldElements as $oldElement) {
                                                $$objectName->$type_description['clear']['removeFunction']($oldElement);
                                                $em->remove($oldElement);
                                            }
                                        }
                                    }
                                }

                                if (count($xmlElement->xpath($type_description['xpath'])) == 0) {
                                    //$$objectName->$function(null);
                                } else {
                                    foreach ($xmlElement->xpath($type_description['xpath']) as $childElement) {
                                        $myChild = $this->xmlElementToEntity(array($type => $type_description), $childElement, $$objectName);
                                        if ($myChild != null) {
                                            $$objectName->$function($myChild);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        if ( count($xmlElement->xpath($xpath)) == 0) {
                            $$objectName->$function(null);
                        } else {
                            $$objectName->$function((string) $xmlElement->xpath($xpath)[0]);
                        }
                    }
                }

                if (isset($description['relationToParent']) && is_string($description['relationToParent'])) {
                    $$objectName->$description['relationToParent']($parentElement);
                }

                $em->persist($$objectName);
                $em->flush();
                $em->refresh($$objectName);
                return $$objectName;

            } else {
                return null;
            }
        }
    }
}