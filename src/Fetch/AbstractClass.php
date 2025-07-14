<?php

namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\Exception\Fetch\DirectoryNotFound;
use DGWebLLC\MimePhpDb\Exception\Fetch\FileWriteError;
use DGWebLLC\MimePhpDb\ConsoleIO;
use Composer\IO\IOInterface;

abstract class AbstractClass {
    public function __construct(string $name = "", IOInterface|null $io = null) {
        // Sets the user-agent header, maybe server configuration are set to automatically reject requests with
        // a proper user-agent
        $this->_context = stream_context_create([
            "http" => [
                "header" => "User-Agent: DGWebLLC-MimePhpDb-HTTPRequest"
            ]
        ]);
        $this->name = $name;
        $this->_io = new ConsoleIO($io);
    }
    /**
     * The context stream to provide to file_get_content when preforming http requests.
     * @var resource
     */
    protected $_context;
    /**
     * Summary of _io
     * @var ConsoleIO
     */
    protected ConsoleIO $_io;
    /**
     * The class name
     * @var string
     */
    protected string $name;
    /**
     * Internal storage for the data table
     * @var array
     */
    protected array $_data = [
        "by-name" => [],
        "by-extension" => [],
    ];
    /**
     * This function fills the internal data table, sorting Mime Type information by both extension and name
     * @return void
     */
    abstract public function fetch();

    public function __get($name) {
        switch ($name) {
            case 'byName':
                return $this->_data['by-name'] ?? [];
            case 'byExtension':
                return $this->_data['by-extension'] ?? [];
        }
    }
    public function __toString() {
        return var_export($this->_data, true);
    }
    /**
     * Writes the internal data table to a valid php array file
     * @param string $location  a file location or null, on null this function will write to the output buffer.
     * @return void
     */
    public function save(string|null $location = null): string {
        if (  $location != null && !is_dir($location.DIRECTORY_SEPARATOR.".") ) {
            throw new DirectoryNotFound("Directory Not Found: {$location}".DIRECTORY_SEPARATOR);
        }

        if ($location != null)
            $file = $location.DIRECTORY_SEPARATOR.$this->name;
        else
            $file = "php://output";

        $this->_io ->write("Writing data source to file: $file");
        $result = file_put_contents($file, "<?php\nreturn ".((string)$this).";\n");

        if ( $result === false ) {
            throw new FileWriteError("Could Not Write File: {$file}");
        }

        return $file;
    }
}