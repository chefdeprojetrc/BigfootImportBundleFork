<?php

namespace Bigfoot\Bundle\ImportBundle\Iterator;

/**
 * Class XmlIterator
 * @package Bigfoot\Bundle\ImportBundle\Iterator
 */
class XmlIterator implements \Iterator
{
    /** @var \DOMDocument */
    protected $content;

    /** @var \DOMDocument */
    protected $currentContent;

    /** @var string */
    protected $xpath;

    /**
     * @param string|\DOMDocument $xml
     * @param string $xpath
     */
    public function __construct($xml, $xpath)
    {
        if ($xml instanceof \DOMDocument) {
            $dom = $xml;
        } else {
            $dom = new \DOMDocument();
            $dom->loadXml($xml);
        }

        $this->content = $dom;
        $this->currentContent = $dom;
        $this->xpath = $xpath;
    }

    /**
     * @inheritdoc
     */
    function rewind()
    {
        $this->currentContent = $this->content;
    }

    /**
     * @inheritdoc
     */
    function current()
    {
        $currentElement = $this->getCurrentElement();

        return $currentElement ? $this->currentContent->saveXML($currentElement) : null;
    }

    /**
     * @inheritdoc
     */
    function key()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    function next()
    {
        $currentElement = $this->getCurrentElement();
        $this->currentContent->removeChild($currentElement);
    }

    /**
     * @inheritdoc
     */
    function valid()
    {
        return (boolean) $this->getCurrentElement();
    }

    /**
     * @return \DOMNode|null
     */
    function getCurrentElement()
    {
        $xpath = new \DOMXPath($this->currentContent);
        $nodes = $xpath->query($this->getXPath());

        return $nodes->length ? $nodes->item(0) : null;
    }

    /**
     * @return string
     */
    function getXPath()
    {
        $xpath = $this->xpath;
        $suffix = '[1]';

        if (strlen($xpath) - strlen($suffix) !== strrpos($xpath, $suffix)) {
            $xpath .= $suffix;
        }

        return $xpath;
    }
}
