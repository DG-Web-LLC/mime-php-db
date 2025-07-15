<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb;

/**
 * 
 */
class MimeDb {
    const DATA_FILE = Config::DATA_DIR.DIRECTORY_SEPARATOR."data";
    /**
     * Summary of _byName
     * @var array
     */
    private array $_byName = [];
    /**
     * Initializes the MimeDB data container
     */
    public function __construct() {
        if ( file_exists(self::DATA_FILE) ) {
            $fdata = file_get_contents(self::DATA_FILE);
            $rows = explode("\n", $fdata);

            foreach ($rows as $row) {
                $mime = new Mime($row);

                $this->_byName[$mime->name] = $mime;
            }
        }
    }
    /**
     * A pass-thru for the array_filter method, the callback provided acts directly on the internal
     * data collection of ['name' => Mime]
     * 
     * Example:
     * 
     * $r = $mimedb->filter(function (Mime $mime) { return $mime->name == 'text/html'; });
     * @param callable $callback
     * @return array
     */
    public function filter(callable $callback) {
        return array_filter($this->_byName, $callback);
    }
}