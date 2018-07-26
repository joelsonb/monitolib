<?php
/**
 * Exception handler
 * @author Joelson B <joelsonb@msn.com>
 * @copyright Copyright &copy; 2018
 *
 * @package MonitoLib
 */
namespace MonitoLib\Exception;

class BadRequest extends \Exception
{
    private $errors = [];

    public function __construct ($message = null, $errors = null, $code = 0, \Exception $previous = null)
    {
        $this->errors = $errors;
        http_response_code(400);
        parent::__construct($message, $code, $previous);
    }

    public function __toString ()
    {
        return $this->errors;
    }
    public function getErrors ()
    {
        return $this->errors;
    }
}