<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    protected $message;
    protected $code;

    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->message,
        ], $this->code);
    }
}
