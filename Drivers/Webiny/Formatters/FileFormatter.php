<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Drivers\Webiny\Formatters;

use Webiny\Component\Logger\Bridge\Webiny\FormatterAbstract;
use Webiny\Component\Logger\Bridge\Webiny\Record;
use Webiny\Component\Logger\Drivers\Webiny\Formatters\Exceptions\FileFormatterException;
use Webiny\Component\Logger\Logger;


/**
 * Formats incoming records into a one-line string
 *
 * @package         Webiny\Component\Logger\Formatters
 */
class FileFormatter extends FormatterAbstract
{
    protected $_format;

    protected $_dateFormat;

    /**
     * @param string $format     The format of the message
     * @param string $dateFormat The format of the timestamp: one supported by DateTime::format
     *
     * @throws Exceptions\FileFormatterException
     */
    public function __construct($format = null, $dateFormat = null)
    {
        $this->_config = Logger::getConfig()->get('Configs.Formatters.File');
        if ($this->isNull($this->_config)) {
            throw new FileFormatterException(FileFormatterException::CONFIG_NOT_FOUND);
        }
        if ($this->isNull($format)) {
            $format = str_replace('\n', "\n", $this->_config->RecordFormat);
        }

        $this->_format = $format;
        $this->dateFormat = $dateFormat !== null ? $dateFormat : $this->_config->DateFormat;
    }

    public function formatRecord(Record $record)
    {

        // Call this to execute standard value normalization
        $record = $this->normalizeValues($record);

        $output = $this->str($this->_format);

        // Handle extra values if case specific values are given in record format
        foreach ($record->extra as $var => $val) {
            if ($output->contains('%extra.' . $var . '%')) {
                $output->replace('%extra.' . $var . '%', $val);
                unset($record->extra[$var]);
            }
        }

        // Handle main record values
        foreach ($record as $var => $val) {
            if ($this->isDateTimeObject($val)) {
                $val = $val->format($this->dateFormat);
            }
            if (is_object($val)) {
                if (method_exists($val, '__toString')) {
                    $val = '' . $val;
                }
            } elseif (is_array($val)) {
                $val = json_encode($val);
            }
            $output->replace('%' . $var . '%', $val);
        }

        $record->formatted = $output->val();

        return $output->val();
    }

    public function formatRecords(array $records, Record $record)
    {
        $message = '';
        foreach ($records as $r) {
            $message .= $this->formatRecord($r);
        }

        $record->formatted = $message;
    }
}
