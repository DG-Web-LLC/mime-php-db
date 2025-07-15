<?php

namespace DGWebLLC\MimePhpDb\Exception\Mime;

use \Exception;
use \Throwable;

class ItemNotEqual extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}