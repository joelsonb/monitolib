<?php
/**
 * Exception handler
 * @author Joelson B <joelsonb@msn.com>
 * @copyright Copyright &copy; 2018
 *  
 * @package MonitoLib
 */
namespace MonitoLib;

class Exception extends \Exception
{
    // protected $message = 'Unknown exception3';   // exception message
    private   $string;                          // __toString cache
    protected $code = 0;                        // user defined exception code
    protected $file;                            // source filename of exception
    protected $line;                            // source line of exception
    private   $trace;                           // backtrace
    private   $previous;                        // previous exception if nested exception
    private $errors = ['k'];

    public function __construct ($message = null, $errors = null, $code = 0, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    // final private function __clone();           // Inhibits cloning of exceptions.

    // final public function getMessage()        // message of exception
    // {
    //     return parent::getMessage();
    // }
    // final public function getCode()           // code of exception
    // {
    //     return parent::getCode();
    // }
    // final public function getFile()           // source filename
    // {
    //     return parent::getFile();
    // }
    // final public function getLine()           // source line
    // {
    //     return parent::getLine();
    // }
    // final public function getTrace()          // an array of the backtrace()
    // {
    //     return parent::getTrace();
    // }
    // final public function getPrevious()       // previous exception
    // {
    //     return parent::getPrevious();
    // }
    // final public function getTraceAsString()  // formatted string of trace
    // {
    //     return parent::getTraceAsString();
    // }

    // Overrideable
    public function __toString ()               // formatted string for display
    {
        return $this->errors;
    }
    public function getErrors ()
    {
        return $this->errors;
    }
}