<?php
// throw exceptions for php errors
function exception_error_handler($errno, $errstr, $errfile, $errline) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// set error reporting level
error_reporting(E_ERROR | E_WARNING | E_PARSE);
?>