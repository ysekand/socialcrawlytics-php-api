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