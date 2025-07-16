<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;

/**
 * MimeDb - A wrapper class that serializes the Media Type Data into an iterable and indexable collection
 * 
 */
class MimeDb implements ArrayAccess, Iterator, Countable {
    const DATA_FILE = Config::DATA_DIR.DIRECTORY_SEPARATOR."data";
    /**
     * Summary of _data
     * @var array
     */
    private array $_data = [];
    private array $_keys = [];
    /**
     * Initializes the MimeDB data container
     */
    public function __construct(string $dataFile = self::DATA_FILE) {
        if ( file_exists($dataFile) ) {
            $fData = file_get_contents($dataFile);
            $rows = explode("\n", $fData);

            foreach ($rows as $row) {
                $mime = new Mime($row);

                $this->_data[$mime->name] = $mime;
                $this->_keys[] = $mime->name;
            }
        } else {
            trigger_error("Data File Not Found", E_USER_WARNING);
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
        return array_filter($this->_data, $callback);
    }

    // ArrayAccess
    public function offsetExists(mixed $offset): bool {
        return isset($this->_data[$offset]);
    }
    public function offsetGet(mixed $offset): mixed {
        return $this->_data[$offset];
    }
    public function offsetSet(mixed $offset, mixed $value): void {
        throw new Exception("Object is readonly");
    }
    public function offsetUnset(mixed $offset): void {
        throw new Exception("Object is readonly");
    }
    // End ArrayAccess
    
    // Iterator
    private int $pos = 0;
    public function current(): mixed {
        return $this->_data[$this->_keys[$this->pos]];
    }
    public function key(): mixed {
        return $this->_keys[$this->pos];
    }
    public function next(): void {
        $this->pos++;
    }
    public function rewind(): void {
        $this->pos = 0;
    }
    public function valid(): bool {
        return isset($this->_keys[$this->pos]);
    }
    // End Iterator

    // Countable
    public function count(): int {
        return count($this->_keys);
    }
    // End Countable

    // TODO: Implement LINQ Like query operators
}