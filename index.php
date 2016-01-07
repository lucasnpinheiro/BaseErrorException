<?php
/**
 * filename: index.php
 *
 * @author Dawid Przygodzki <godzki18@gmail.com>
 * @copyright Copyright (c) 2016, Dawid Przygodzki
 * @link dawidprzygodzki.pl
 * @since 1.0.0
 * @date 06.01.2016
 * @IDE PhpStorm
 */

define('DEV_ENV', false);

// production environment
error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);

// development environment
if(DEV_ENV){
    ini_set("display_errors", 1);
}

// include and init BaseErrorException
include_once 'BaseErrorException.php';
$baseErrorException = new \Base\BaseErrorException();

// test BaseErrorException
//trigger_error("E_USER_ERROR", E_USER_ERROR);
//trigger_error("E_USER_NOTICE", E_USER_NOTICE);
//trigger_error("E_USER_DEPRECATED", E_USER_DEPRECATED);