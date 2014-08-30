<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Logger\Bridge\Webiny;

use Webiny\Component\Logger\Bridge\LoggerException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\Logger\Bridge\Webiny\Record;

/**
 * Base Handler class providing the Handler structure
 * @package Webiny\Component\Logger\Bridge\Webiny
 */
abstract class HandlerAbstract
{
    use StdLibTrait;

    protected $_levels = [];
    protected $_bubble = true;
    protected $_buffer = false;

    /**
     * @var FormatterInterface
     */
    protected $_formatter = null;
    protected $_processors = [];
    protected $_records = [];

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param Record $record
     *
     * @return void
     */
    abstract protected function write(Record $record);

    /**
     * Get default formatter for this handler
     *
     * @return FormatterAbstract
     */
    abstract protected function _getDefaultFormatter();

    /**
     * @param array|ArrayObject $levels The minimum logging level at which this handler will be triggered
     * @param Boolean           $bubble Whether the messages that are handled can bubble up the stack or not
     * @param bool              $buffer
     *
     * @return HandlerAbstract Instance of HandlerAbstract
     */
    public function __construct($levels = [], $bubble = true, $buffer = false)
    {
        $this->_levels = $this->arr($levels);
        $this->_bubble = $bubble;
        $this->_buffer = $buffer;
        $this->_processors = $this->arr();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        try {
            $this->stopHandling();
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * Check if this handler can handle the given Record
     *
     * @param Record $record
     *
     * @return bool Boolean telling whether this handler can handle the given Record
     */
    public function canHandle(Record $record)
    {
        if ($this->_levels->count() < 1) {
            return true;
        }

        return $this->_levels->inArray($record->level);
    }

    /**
     * Stop handling<br />
     * This will be called automatically when the object is destroyed
     *
     * @return void
     */
    public function stopHandling()
    {
        if ($this->_buffer) {
            $this->_processRecords($this->_records);
        }
    }

    /**
     * Add processor to this handler
     *
     * @param mixed $callback Callable or instance of ProcessorInterface
     *
     * @param bool  $processBufferRecord
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function addProcessor($callback, $processBufferRecord = false)
    {
        if (!is_callable($callback) && !$this->isInstanceOf($callback,
                                                            '\Webiny\Component\Logger\Bridge\Webiny\ProcessorInterface'
            )
        ) {
            throw new \InvalidArgumentException('Processor must be valid callable or an instance of \Webiny\Component\Logger\Bridge\Webiny\ProcessorInterface');
        }

        if ($processBufferRecord) {
            $this->_bufferProcessors->prepend($callback);
        } else {
            $this->_processors->prepend($callback);
        }


        return $this;
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->_formatter = $formatter;

        return $this;
    }

    /**
     * Process given record
     * This will pass given record to ProcessorInterface instance, then format the record and output it according to current HandlerAbstract instance
     *
     * @param Record $record
     *
     * @return bool Bubble flag (this either continues propagation of the Record to other handlers, or stops the logger from processing this record any further)
     */
    public function process(Record $record)
    {

        if ($this->_buffer) {
            $this->_processRecord($record);
            $this->_records[] = $record;

            return $this->_bubble;
        }

        $this->_processRecord($record);
        $this->_getFormatter()->formatRecord($record);
        $this->write($record);

        return $this->_bubble;
    }

    /**
     * Processes a record.
     *
     * @param Record $record
     *
     * @return Record Processed Record object
     */
    protected function _processRecord(Record $record)
    {

        foreach ($this->_processors as $processor) {
            if ($this->isInstanceOf($processor, '\Webiny\Component\Logger\Bridge\Webiny\ProcessorInterface')) {
                $processor->processRecord($record);
            } else {
                call_user_func($processor, $record);
            }
        }
    }

    /**
     * Process batch of records
     *
     * @param array $records Batch of records to process
     *
     * @return bool Bubble flag (this either continues propagation of the Record to other handlers, or stops the logger from processing this record any further)
     */
    protected function _processRecords(array $records)
    {
        $record = new Record();
        $formatter = $this->_getFormatter();
        if ($this->isInstanceOf($formatter, '\Webiny\Component\Logger\Bridge\Webiny\FormatterInterface')) {
            $formatter->formatRecords($records, $record);
        }

        $this->write($record);

        return $this->_bubble;
    }

    /**
     * @throws \Webiny\Component\Logger\Bridge\LoggerException
     * @return FormatterInterface Instance of formatter to use for record formatting
     */
    private function _getFormatter()
    {
        if ($this->isNull($this->_formatter)) {
            $this->_formatter = $this->_getDefaultFormatter();
            if (!$this->isInstanceOf($this->_formatter, '\Webiny\Component\Logger\Bridge\Webiny\FormatterInterface')) {
                throw new LoggerException('Formatter must be an instance of \Webiny\Component\Logger\Bridge\Webiny\FormatterInterface');
            }

        }

        return $this->_formatter;
    }
}
