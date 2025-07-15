<?php

namespace DGWebLLC\MimePhpDb\Exception\Mime;

use \Exception;
use \Throwable;

class DeserializationError extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}