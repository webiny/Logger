<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Logger\Bridge\Webiny;

use Webiny\Component\Logger\Bridge\LoggerException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Interface for processors
 * @package Webiny\Component\Logger\Bridge\Webiny
 */
interface ProcessorInterface
{
    /**
     * Processes a log record.
     *
     * @param Record $record A record to format
     *
     * @return Record The formatted record
     */
    public function processRecord(Record $record);
}
