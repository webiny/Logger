<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Drivers\Webiny\Processors;

use Webiny\Component\Logger\Bridge\Webiny\ProcessorInterface;
use Webiny\Component\Logger\Bridge\Webiny\Record;

/**
 * FileLineProcessor adds 'file' and 'line' values to the Record 'extra' data
 *
 * @package Webiny\Component\Logger\Drivers\Webiny\Processors
 */
class FileLineProcessor implements ProcessorInterface
{

    /**
     * Processes a log record.
     *
     * @param Record $record A record to format
     *
     * @return Record The formatted record
     */
    public function processRecord(Record $record)
    {

        $backtrace = debug_backtrace();
        $backtrace = $backtrace[5];

        $record->extra['file'] = $backtrace['file'];
        $record->extra['line'] = $backtrace['line'];
    }
}