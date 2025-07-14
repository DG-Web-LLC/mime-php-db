<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb;

/**
 * 
 */
class MimeDb {
    const DATA_FILE = __DIR__.DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."data";
    private array $_data;

    public function __construct() {
        if ( file_exists(self::DATA_FILE) ) {
            $this->_data = require(self::DATA_FILE);
        }
    }
}