<?php

namespace mix\exception;
use Throwable;

/**
 * EndException类
 * @author 刘健 <coder.liu@qq.com>
 */
class Err extends \RuntimeException
{
    public $message = array();
    public function __construct($message = "", $code = 0,$char = array(), Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = array(
            'code' => $code,
            'data' => $char,
            'msg' => $message,
        );
    }
}
