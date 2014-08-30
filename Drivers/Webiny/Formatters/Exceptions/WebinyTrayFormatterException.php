<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */
namespace Webiny\Component\Logger\Drivers\Webiny\Formatters\Exceptions;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;


/**
 * Udp handler exception class.
 *
 * @package      Webiny\Component\Logger\Drivers\Webiny\Handlers\Exceptions
 */
class WebinyTrayFormatterException extends ExceptionAbstract
{
    const CONFIG_NOT_FOUND = 101;

    protected static $_messages = [
        101 => 'Webiny tray formatter config was not found!',
    ];
}