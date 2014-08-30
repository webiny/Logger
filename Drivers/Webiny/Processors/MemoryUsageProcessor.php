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
 * MemoryUsageProcessor adds 'memory_usage' (current allocated amount of memory) to the Record 'extra' data
 *
 * @package Webiny\Component\Logger\Drivers\Webiny\Processors
 */
class MemoryUsageProcessor implements ProcessorInterface
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
        $record->extra['memory_usage'] = memory_get_usage(true);
    }
}