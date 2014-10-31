<?php

namespace Bigfoot\Bundle\ImportBundle\Extractor;

/**
 * Class XmlExtractor
 * @package Bigfoot\Bundle\ImportBundle\Extractor
 */
class XmlExtractor
{
    /**
     * @param string|\DOMDocument $input
     * @param string $xpath
     * @return string
     */
    public static function extract(&$input, $xpath)
    {
        if ($input instanceof \DOMDocument) {
            $dom = $input;
        } else {
            $dom = new \DOMDocument();
            @$dom->loadXML($input);
        }

        $domXpath = new \DOMXPath($dom);
        $nodes = $domXpath->query($xpath);
        $content = '';

        /** @var \DOMNode $node */
        foreach ($nodes as $node) {
            $content .= $dom->saveXML($node);
            $node->parentNode->removeChild($node);
        }

        if (is_string($input)) {
            $input = $dom->saveXML($dom->documentElement);
        }

        return $content;
    }
}
