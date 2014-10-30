<?php

namespace Bigfoot\Bundle\ImportBundle\Client;

/**
 * Class SoapClient
 * @package Bigfoot\Bundle\ImportBundle\Client
 */
class SoapClient extends \SoapClient
{
    /** @var string */
    protected $requestHeaders;

    /** @var string */
    protected $responseHeaders;

    /** @var string */
    protected $responseBody;

    /** @var bool */
    protected $requestInnerXml = false;

    /**
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param bool $one_way
     * @return string
     */
    public function __doRequest($request, $location, $action, $version = 1, $one_way = false)
    {
        $soap = new \DOMDocument('1.0', 'UTF-8');
        $soap->loadXML('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Header/><soap:Body/></soap:Envelope>');

        if ($this->requestHeaders) {
            $headerDom = new \DOMDocument('1.0', 'UTF-8');
            $headerDom->loadXML($this->requestHeaders);
            $headerNode = $soap->importNode($headerDom, true);
            $soap->firstChild->firstChild->appendChild($headerNode);
        }

        $requestDom = new \DOMDocument('1.0', 'UTF-8');
        $requestDom->loadXML($request);

        if ($this->requestInnerXml) {
            foreach ($requestDom->firstChild->childNodes as $node) {
                $requestNode = $soap->importNode($node, true);
                $soap->firstChild->firstChild->nextSibling->appendChild($requestNode);
            }
        } else {
            $requestNode = $soap->importNode($requestDom->documentElement, true);
            $soap->firstChild->firstChild->nextSibling->appendChild($requestNode);
        }

        $request = $soap->saveXML($soap->documentElement);
        $responseBody = '';
        $responseHeader = '';

        $response = parent::__doRequest($request, $location, $action, $version);
        $soap = new \DOMDocument('1.0', 'UTF-8');
        $soap->loadXML($response);

        $xpath = new \DOMXPath($soap);
        $xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');

        foreach ($xpath->query('/soap:Envelope/soap:Body/*') as $responseNode) {
            $responseBody .= $soap->saveXML($responseNode);
        }

        foreach ($xpath->query('/soap:Envelope/soap:Header/*') as $responseNode) {
            $responseHeader .= $soap->saveXML($responseNode);
        }

        $this->responseHeader = $responseHeader;
        $this->responseBody = $responseBody;

        return $responseBody;
    }

    /**
     * @param $requestHeaders
     * @return $this
     */
    public function setRequestHeaders($requestHeaders)
    {
        $this->requestHeaders = $requestHeaders;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * @param $responseHeaders
     * @return $this
     */
    public function setResponseHeaders($responseHeaders)
    {
        $this->responseHeaders = $responseHeaders;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * @param $responseBody
     * @return $this
     */
    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @param boolean $requestInnerXml
     * @return $this
     */
    public function setRequestInnerXml($requestInnerXml)
    {
        $this->requestInnerXml = $requestInnerXml;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequestInnerXml()
    {
        return $this->requestInnerXml;
    }
}
