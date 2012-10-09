<?php

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

namespace SC\Api;

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