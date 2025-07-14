<?php

namespace DGWebLLC\MimePhpDb\Exception\Fetch;

use \Exception;
use \Throwable;

class DirectoryNotFound extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}