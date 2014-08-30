<?php

namespace Webiny\Component\Logger\Bridge\Webiny;

/**
 * Logger record container class
 *
 * @package Webiny\Component\Logger\Bridge\Webiny
 */

class Record implements \IteratorAggregate
{
    public $name;
    public $message;
    public $level;
    public $context;
    public $datetime;
    public $extra = [];
    public $formatted;

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

}