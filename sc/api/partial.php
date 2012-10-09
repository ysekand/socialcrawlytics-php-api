<?php

/**
 * API Partial class
 *
 * Partials are part page updates
 *
 * @copyright   Yousaf Sekander 2012 <y.sekander@hotmail.co.uk>
 * @package     Social Crawlytics
 */

namespace SC\Api;

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