<?php

/**
 * API Transaction Class
 *
 * Transactions are responses from the server when an operation
 * has been completed via AJAX
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

namespace SC\Api;

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