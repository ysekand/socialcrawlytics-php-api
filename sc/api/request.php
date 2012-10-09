<?php

/**
 * API Request Class
 *
 * This class handles forging requests to our eAPI server to
 * allow for a much nicer method of dealing with the API in PHP
 * user code.
 *
 * We also add support for callbacks which are passed to the
 * Response class so that we can keep the main execution loop clean.
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

namespace SC\Api;

class Request {

    // Our eAPI system supports XML, however this library was built around JSON.
    protected $allowedFormats = array('json');
    protected $format       = 'json';

    protected $eapiServer   = 'https://socialcrawlytics.com/eapi/';
    protected $addressMap   = '%s%s/%s.%s?token=%s&key=%s';
    protected $callbacks    = array();

    private $token = null;
    private $key   = null;

    public function __construct($token = null, $key = null, $format = null, $server = null)
    {

        // Allow changing of the eAPI server for future use
        if (!empty($server)) {
            $this->eapiServer = $server;
        }

        // No point proceeding unless we have a token / key pair
        if (empty($token) || empty($key)) {
            throw new \Exception('Invalid token / key pair, please ensure you supply both credentials.');
        } else {
            $this->token = $token;
            $this->key   = $key;
        }

        // If they specify a format, let's make sure it's supported
        if (!empty($format)) {

            if (!in_array($format, $this->allowedFormats)) {
                throw new \Exception('Invalid API format, known API formats [' . trim(implode(',', $this->allowedFormats), ' ,') . ']');
            } else {
                $this->format = $format;
            }

        }

    }

    public function callback($name, callable $function)
    {
        $this->callbacks['_eapi_' . $name][] = $function;
    }

    public function __call($name, $arguments)
    {

        $fracture = explode('_', $name);
        $params   = (is_array($arguments[0])) ? $arguments[0] : array();

        if (strtoupper($fracture[0]) == 'GET') {

            $address = sprintf($this->addressMap, $this->eapiServer, $fracture[1], $fracture[2], $this->format, $this->token, $this->key);

            foreach ($params as $param => $value) {
                $address .= '&' . urlencode($param) . '=' . urlencode($value);
            }

            $curl       =   curl_init();
                            curl_setopt($curl, CURLOPT_URL, $address);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                            curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . "trust.crt");

            $content    =   curl_exec($curl);
                            curl_close($curl);

            return new Response($content, $this->callbacks);
        }

        if (strtoupper($fracture[0]) == 'POST') {

            $address = sprintf($this->addressMap, $this->eapiServer, $fracture[1], $fracture[2], $this->format, $this->token, $this->key);
            $payload = '';

            foreach ($params as $param => $value) {
                $payload .= '&' . urlencode($param) . '=' . urlencode($value);
            }

            $curl       =   curl_init();
                            curl_setopt($curl, CURLOPT_URL, $address);
                            curl_setopt($curl, CURLOPT_POST, 1);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, trim($payload, '&'));
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                            curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . "trust.crt");

            $content    =   curl_exec($curl);
                            curl_close($curl);

            return new Response($content, $this->callbacks);

        }

    }

}