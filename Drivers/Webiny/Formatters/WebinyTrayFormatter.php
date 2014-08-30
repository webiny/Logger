<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Drivers\Webiny\Formatters;

use Webiny\Component\Logger\Bridge\Webiny\FormatterAbstract;
use Webiny\Component\Logger\Bridge\Webiny\Record;
use Webiny\Component\Logger\Drivers\Webiny\Formatters\Exceptions\WebinyTrayFormatterException;


/**
 * Formats incoming records into a request for Webiny Tray Notifier
 *
 * @package         Webiny\Component\Logger\Formatters
 */
class WebinyTrayFormatter extends FormatterAbstract
{

    /**
     * Message date format
     * @var string
     */
    protected $_dateFormat;

    /**
     * JsonRPC method to call
     * @var string
     */
    private $_method;

    private $_tray;

    /**
     * @param string|null $method     JsonRPC method to call
     * @param string|null $dateFormat The format of the timestamp: one supported by DateTime::format
     *
     * @throws Exceptions\WebinyTrayFormatterException
     */
    public function __construct($method = null, $dateFormat = null)
    {
        $this->_config = Logger::getConfig()->get('Configs.Formatters.WebinyTray');
        if ($this->isNull($this->_config)) {
            throw new WebinyTrayFormatterException(WebinyTrayFormatterException::CONFIG_NOT_FOUND);
        }
        $this->_dateFormat = $dateFormat !== null ? $dateFormat : $this->_config->DateFormat;
        $this->_method = $method !== null ? $method : $this->_config->Method;

        $tray = $this->_config->Tray;

        if ($this->isInstanceOf($tray, 'Webiny\Component\Config\ConfigObject')) {
            $tray = $tray->toArray();
        }

        if (!$this->isArray($tray) && !$this->isArrayObject($tray)) {
            $tray = [$tray];
        }

        $this->_tray = $tray;
    }

    public function formatRecord(Record $record)
    {

        // Call this to execute standard value normalization
        $record = $this->normalizeValues($record);

        $output = [];

        $extra = $this->arr($record->extra);
        if ($extra->keyExists('line') && $extra->keyExists('file')) {
            $output['line'] = $extra['line'];
            $output['file'] = $extra['file'];
            $extra->removeKey('line')->removeKey('file');
        }

        if ($extra->keyExists('memory_usage')) {
            $output['memory'] = $extra['memory_usage'];
            $extra->removeKey('memory_usage');
        }

        $record->extra = $extra->val();

        // Handle main record values
        foreach ($record as $var => $val) {
            if ($this->isDateTimeObject($val)) {
                $val = $val->format($this->dateFormat);
            }
            if ($this->isObject($val)) {
                $val = (array)$val;
            } elseif ($this->isArray($val)) {
                $val = json_encode($val);
            }

            $output[$var] = $val;
        }

        return $output;
    }

    public function formatRecords(array $records, Record $record)
    {

        $request = [
            'memory'   => memory_get_peak_usage(true),
            'datetime' => $this->datetime("now")->format($this->_config->DateFormat),
            'url'      => $_SERVER["REQUEST_URI"],
            'get'      => $_GET,
            'post'     => $_POST,
            'server'   => $_SERVER,
            'level'    => ''
        ];

        // Building array like this saves us loads of "if" statements later in the loop
        $keys = [
            'debug'     => 100,
            'info'      => 200,
            'notice'    => 250,
            'warning'   => 300,
            'error'     => 400,
            'critical'  => 500,
            'alert'     => 550,
            'emergency' => 600
        ];

        $levels = array_keys($keys);
        $stats = $this->arr($levels)->fillKeys(0)->val();

        /* @var $rec Record */
        foreach ($records as $rec) {
            unset($rec->formatted);
            $request['messages'][] = $this->formatRecord($rec);
            $stats[$rec->level]++;
        }

        // Remove loge levels which are empty
        $stats = array_filter($stats);

        // Determine highest log level
        $highestLevelCode = 0;
        foreach ($records as $rec) {
            $levelCode = $keys[$rec->level];
            if ($levelCode > $highestLevelCode) {
                $highestLevelCode = $levelCode;
                $request['level'] = $rec->level;
            }
        }

        $request['stats'] = $stats;

        $json = [
            'jsonrpc' => '2.0',
            'method'  => $this->_method,
            'params'  => [
                'tray'    => $this->_tray,
                'request' => $request
            ]
        ];

        $record->formatted = $this->jsonEncode($json);
    }
}