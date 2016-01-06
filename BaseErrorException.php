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
    // template name
    public $template = 'base';

    // error data to output
    public $errorData = [];

    // error severity type options
    public $severityOptions = [
        1       => ['name' => 'E_ERROR', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        2       => ['name' => 'E_WARNING', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        4       => ['name' => 'E_PARSE', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        8       => ['name' => 'E_NOTICE', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        16      => ['name' => 'E_CORE_ERROR', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        32      => ['name' => 'E_CORE_WARNING', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        64      => ['name' => 'E_COMPILE_ERROR', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        128     => ['name' => 'E_COMPILE_WARNING', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        256     => ['name' => 'E_USER_ERROR', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        512     => ['name' => 'E_USER_WARNING', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        1024    => ['name' => 'E_USER_NOTICE', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        2048    => ['name' => 'E_STRICT', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        4096    => ['name' => 'E_RECOVERABLE_ERROR', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        8192    => ['name' => 'E_DEPRECATED', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
        16384   => ['name' => 'E_USER_DEPRECATED', 'error_color' => '#ffffff', 'error_background' => '#ff0000'],
    ];

    public function __construct()
    {
        // register a function for execution on shutdown
        register_shutdown_function(array($this, 'checkForFatal'));
        // sets a user-defined error handler function
        set_error_handler(array($this, 'errorHandler'));
        // sets a user-defined exception handler function
        set_exception_handler(array($this, 'logException'));
    }

    // check for fatal error
    public function checkForFatal()
    {
        // get the last occurred error
        $error = error_get_last();

        if ($error["type"] == E_ERROR) {
            $this->logError($error["type"], $error["message"], $error["file"], $error["line"]);
        }
    }

    // error handler
    function errorHandler($num, $str, $file, $line, $context = null)
    {
        $this->logException(new \ErrorException($str, 0, $num, $file, $line));
    }

    // save log in log file
    function logException(\Exception $error)
    {
        $severity = $error->getSeverity();
        $this->prepareErrorData($error, $severity);

        // log template message
        $message = '[' . date('d-m-Y H:i:s') . '] ';
        $message .= '[' . get_class($error) . ']';
        $message .= 'Message: ' . $error->getMessage() . '; ';
        $message .= 'File: ' . $error->getFile() . '; ';
        $message .= 'Line: ' . $error->getLine() . ';';

        // save log
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'error' . DIRECTORY_SEPARATOR . $this->severityOptions[$severity]['name'] . '.log', $message . PHP_EOL, FILE_APPEND);

        if ($severity == E_ERROR || $severity == E_USER_ERROR) {
            exit;
        }
    }

    // prepare error
    public function prepareErrorData($error, $severity)
    {
        $error_type = [
            'name' => 'Undefined Error',
            'error_color' => '#000000',
            'error_background' => '#eeeeeee'
        ];

        foreach ($this->severityOptions as $key => $options) {
            if ($severity === $key) {
                $error_type = $options;
            }
        }

        $this->errorData = [
            'exception_name' => get_class($error),
            'name' => $error_type['name'],
            'error_color' => $error_type['error_color'],
            'error_background' => $error_type['error_background'],
            'error_message' => $error->getMessage(),
            'error_file' => $error->getFile(),
            'error_line' => $error->getLine(),
        ];

        $this->showError();
    }

    // display error using error template file
    public function showError()
    {
        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $this->template . '.html');

        foreach ($this->errorData as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        echo $content;
    }
}