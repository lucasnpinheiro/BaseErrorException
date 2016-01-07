<?php
/**
 * filename: BaseErrorException.php
 *
 * @author Dawid Przygodzki <godzki18@gmail.com>
 * @copyright Copyright (c) 2016, Dawid Przygodzki
 * @link dawidprzygodzki.pl
 * @since 1.0.0
 * @date 06.01.2016
 * @IDE PhpStorm
 */

namespace Base;

class BaseErrorException
{
    // error data to output
    public $errorData = [];

    // error list
    public static $errorList = [];

    // error code
    public $errorCode = [
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR',
        32 => 'E_CORE_WARNING',
        64 => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024 => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED'
    ];

    public function __construct()
    {
        if (!defined('ERROR_PATH')) {
            define('ERROR_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'error' . DIRECTORY_SEPARATOR);
        }

        // register a function for execution on shutdown
        register_shutdown_function(array($this, 'onShutdown'));
        // sets a user-defined error handler function
        set_error_handler(array($this, 'errorHandler'));
        // sets a user-defined exception handler function
        set_exception_handler(array($this, 'logException'));
    }

    // check for fatal error
    public function onShutdown()
    {
        // get the last occurred error
        $error = error_get_last();

        if ($error["type"] == E_ERROR) {
            self::$errorList[] = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        }

        if (count(self::$errorList)) {
            // errors sorted from last to first
            self::$errorList = array_reverse(self::$errorList);

            $html = '<html>
                        <head>
                            <title>ErrorException</title>
                            <style>
                            html,body{font-family: Arial;}
                            table{width: 100%; border-collapse: collapse;}
                            table tr th{background-color: #6f5499; color: #fff; padding: 10px; text-align: left; border: 1px solid #6f5499;}
                            table tr td{padding: 10px; vertical-align: top; border: 1px solid #6f5499;}
                            .error{color: #333;}
                            .e_error{color: #ff0000;}
                            .e_warning{color: #ffa500;}
                            .e_parse{color: #0059ff;}
                            .e_notice{color: #00af00;}
                            .e_core_error{color: #ff0000;}
                            .e_core_warning{color: #ffa500;}
                            .e_compile_error{color: #ff0000;}
                            .e_compile_warning{color: #ffa500;}
                            .e_user_error{color: #ff0000;}
                            .e_user_warning{color: #ffa500;}
                            .e_user_notice{color: #00af00;}
                            .e_strict{color: #00b0b0;}
                            .e_recoverable_error{color: #ff0000;}
                            .e_deprecated{color: #006060;}
                            .e_user_deprecated{color: #006060;}
                        </style>
                        </head>
                        <body>';

            $html .= '<table>
                          <tr>
                              <th>Type</th>
                              <th>Message</th>
                          </tr>';
        }

        foreach (self::$errorList as $error) {
            $html .= $this->logException($error);
        }

        $html .= '</table>
                  </body>
                  </html>';

        echo $html;
    }

    // error handler
    function errorHandler($num, $str, $file, $line, $context = null)
    {
        self::$errorList[] = new \ErrorException($str, 0, $num, $file, $line);
    }

    // save log in log file
    function logException(\Exception $error)
    {
        $severity = $error->getSeverity();

        // set error data
        $this->errorData = [
            'exception_name' => get_class($error),
            'name' => $this->errorCode[$error->getSeverity()],
            'error_message' => $error->getMessage(),
            'error_file' => $error->getFile(),
            'error_line' => $error->getLine(),
        ];

        // log template message
        $message = '[' . date('d-m-Y H:i:s') . '] ';
        $message .= '[' . get_class($error) . ']';
        $message .= 'Message: ' . $error->getMessage() . '; ';
        $message .= 'File: ' . $error->getFile() . '; ';
        $message .= 'Line: ' . $error->getLine() . ';';

        // check error dir exist
        if (!file_exists(ERROR_PATH)) {
            mkdir(ERROR_PATH, 0777, true);
        }

        // save log
        file_put_contents(ERROR_PATH . $this->errorCode[$severity] . '.log', $message . PHP_EOL, FILE_APPEND);

        return $this->showError();
    }

    // display error using error template file
    public function showError()
    {
        return $content = '<tr class="error ' . strtolower($this->errorData['name']) . '">
                                <td>' . '[' . $this->errorData['name'] . ']<br/>' . $this->errorData['exception_name'] . '()</td>
                                <td>
                                    ' . $this->errorData['error_message'] . '<br/>
                                    ' . $this->errorData['error_file'] . ':' . $this->errorData['error_line'] . '
                                </td>
                            </tr>';
    }
}