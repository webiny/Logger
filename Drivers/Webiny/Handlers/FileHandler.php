<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Drivers\Webiny\Handlers;

use Webiny\Component\Logger\Bridge\Webiny\FormatterAbstract;
use Webiny\Component\Logger\Bridge\Webiny\HandlerAbstract;
use Webiny\Component\Logger\Bridge\Webiny\Record;
use Webiny\Component\Logger\Drivers\Webiny\Formatters\FileFormatter;
use Webiny\Component\Logger\LoggerException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectException;

/**
 * FileHandler class stores log messages to log file
 *
 * @package         Webiny\Component\Logger\Drivers\Webiny\Handlers
 */
class FileHandler extends HandlerAbstract
{
    use StdLibTrait;

    private $_file;

    public function __construct($file, $levels = [], $bubble = true, $buffer = false)
    {
        parent::__construct($levels, $bubble, $buffer);
        try {
            $this->_file = $file;
        } catch (StdObjectException $e) {
            throw new LoggerException($e->getMessage());
        }

    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param Record $record
     *
     * @return void
     */
    protected function write(Record $record)
    {
        file_put_contents($this->_file, $record->formatted, FILE_APPEND);
    }

    /**
     * Get default formatter for this handler
     *
     * @return FormatterAbstract
     */
    protected function _getDefaultFormatter()
    {
        return new FileFormatter();
    }
}