<?php

/**
 * API Error Class
 *
 * Errors arising over AJAX are handled by this class
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

namespace SC\Api;

class Error {

    public $mark;
    public $source;
    public $results;
    public $dataset;

    public function __construct($mark = null, $source = null, $results = null, $dataset = null) {
        $this->mark    = $mark;
        $this->source  = $source;
        $this->results = $results;
        $this->dataset = $dataset;
    }

}

/**
 * API Partial class
 *
 * Partials are part page updates
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

class Partial {

    public $mark;
    public $source;
    public $results;
    public $dataset;

    public function __construct($mark = null, $source = null, $results = null, $dataset = null) {
        $this->mark    = $mark;
        $this->source  = $source;
        $this->results = $results;
        $this->dataset = $dataset;
    }

}

/**
 * API Transaction Class
 *
 * Transactions are responses from the server when an operation
 * has been completed via AJAX
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

class Transaction {

    public $mark;
    public $source;
    public $results;
    public $dataset;

    public function __construct($mark = null, $source = null, $results = null, $dataset = null) {
        $this->mark    = $mark;
        $this->source  = $source;
        $this->results = $results;
        $this->dataset = $dataset;
    }

}

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

/**
 * API Response Class
 *
 * This class handles the transactions, partials, and errors
 * resulting from API use and will collate them into one final
 * object for return.
 *
 * This allows us to return as many errors, partial updates,
 * and transaction responses as we want in one push.
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

class Response {

    public $errors = array();
    public $partials = array();
    public $transactions = array();

    public function __construct($dataset = null, $callbacks) {

        // If we have a dataset, it means we're parsing a response
        // and should fill objects to iterate through.
        if ($dataset) {
            $dataset = json_decode($dataset);

            foreach ($dataset->_transactions as $transaction) {
                $resource = new Transaction(
                                $transaction->mark,
                                $transaction->source,
                                $transaction->results,
                                $transaction->dataset
                        );

                if (isset($callbacks[$resource->mark])) {
                    foreach ($callbacks[$resource->mark] as $callback) {
                        call_user_func($callback, $resource);
                    }
                }

                $this->bind($resource);
            }

            foreach ($dataset->_errors as $error) {
                $resource = new Error(
                                $error->mark,
                                $error->source,
                                $error->results,
                                $error->dataset
                        );

                if (isset($callbacks[$resource->mark])) {
                    foreach ($callbacks[$resource->mark] as $callback) {
                        call_user_func($callback, $resource);
                    }
                }


                $this->bind($resource);
            }

            foreach ($dataset->_partials as $partial) {
                $resource = new Partial(
                                $partial->mark,
                                $partial->source,
                                $partial->results,
                                $partial->dataset
                        );

                if (isset($callbacks[$resource->mark])) {
                    foreach ($callbacks[$resource->mark] as $callback) {
                        call_user_func($callback, $resource);
                    }
                }


                $this->bind($resource);
            }
        }
    }

    /**
     * Binds an object to our response, expects one of the following objects
     * as a parameter: \SC\Api\Transaction, \SC\Api\Error or
     * \SC\Api\Partial
     *
     * Returns true on success, throws exception if the object is invalid
     *
     * @param $resource
     * @return bool
     * @throws \Exception
     */
    public function bind($resource) {

        if ($resource instanceof \SC\Api\Transaction) {
            array_push($this->transactions, $resource);
            return true;
        }

        if ($resource instanceof \SC\Api\Error) {
            array_push($this->errors, $resource);
            return true;
        }

        if ($resource instanceof \SC\Api\Partial) {
            array_push($this->partials, $resource);
            return true;
        }

        throw new \Exception('Invalid resource type bind attempt to response object');
    }

    /**
     * Collates all AJAX responses currently bound as an array
     *
     * @return array
     */
    public function get() {
        return array(
            '_transactions' => $this->transactions,
            '_partials' => $this->partials,
            '_errors' => $this->errors
        );
    }

    /**
     * If there are errors bound to the response, returns the
     * number of errors, otherwise false
     *
     * @return bool|int
     */
    public function hasErrors() {
        if (count($this->errors) > 0) {
            return count($this->errors);
        } else {
            return FALSE;
        }
    }

}