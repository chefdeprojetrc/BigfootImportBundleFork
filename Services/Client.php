<?php

namespace Bigfoot\Bundle\ImportBundle\Services;

/**
 * FTP class
 *
 * @Author S.huot s.huot@c2is.fr
 */
class Client
{
    protected $domain;

    protected $port;

    protected $username;

    protected $password;

    protected $protocol;

    /**
     * Initialize the Client
     *
     * @param $protocol String Protocol
     * @param $domain String IP Address or domain name
     * @param $port Integer Port of the address
     */
    public function init($protocol, $domain, $port = 21)
    {
        $this->protocol = $protocol;
        $this->domain = $domain;
        $this->port   = $port;
    }

    /**
     * Credentials of the client
     *
     * @param $username String Login of the FTP
     * @param $password String Password of the FTP
     */
    public function setAuth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get the distant file by Curl Method
     *
     * @param $uri String Name of the distant file
     * @return string
     */
    public function get($uri)
    {
        $url      = sprintf("%s://%s/%s", $this->protocol, $this->domain, trim($uri, '/'));
        $filename = sprintf("/tmp/%s", uniqid());

        $curl = curl_init();
        $file = fopen($filename, 'w');
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FILE, $file);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf("%s:%s", $this->username, $this->password));
        curl_exec($curl);
        curl_close($curl);

        fclose($file);

        return $filename;
    }
}


