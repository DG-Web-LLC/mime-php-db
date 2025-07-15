<?php

namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\Config;
use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Mime;
use DGWebLLC\MimePhpDb\Exception\Fetch\DirectoryNotFound;
use DGWebLLC\MimePhpDb\Exception\Fetch\FileWriteError;
use DGWebLLC\MimePhpDb\Exception\Fetch\HttpFetchError;
use Composer\IO\IOInterface;
/**
 * Summary of AbstractClass
 */
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
     * @var Mime[]
     */
    protected array $_data = [];
    /**
     * This function fills the internal data table, sorting Mime Type information by both extension and name
     * @return void
     */
    abstract public function fetch();

    /**
     * Summary of __toString
     * @return string
     */
    public function __toString() {
        return implode("\n", $this->_data);
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
        $result = file_put_contents($file, (string)$this);

        if ( $result === false ) {
            throw new FileWriteError("Could Not Write File: {$file}");
        }

        return $file;
    }
    /**
     * Summary of addMime
     * @param \DGWebLLC\MimePhpDb\Mime $mime
     * @return void
     */
    public function addMime(Mime $mime) {
        if ( !isset($this->_data[$mime->name]) ) {
            $this->_data[$mime->name] = $mime;
        } else {
            $this->_data[$mime->name]->merge($mime);
        }
    }
    /**
     * Summary of removeMime
     * @param \DGWebLLC\MimePhpDb\Mime $mime
     * @return void
     */
    public function removeMime(Mime $mime) {
        if ( isset($this->_data[$mime->name]) ) {
            unset($this->_data[$mime->name]);
        }
    }
    /**
     * Summary of httpRequest
     * @param string $url
     * @throws \DGWebLLC\MimePhpDb\Exception\Fetch\HttpFetchError
     * @return string
     */
    protected function httpRequest(string $url): string {
        $data = false;
        $attempts = 0;

        while ($data === false && $attempts < Config::HTTP_ATTEMPTS) {
            $data = @file_get_contents($url, false, $this->_context);
            $attempts++;

            if ($data === false) {
                // Allows a delay between fetch requests
                sleep(1);
            }
        }

        if ($data === false) {
            throw new HttpFetchError(
                "HTTP Resource Unreachable: $url\n".
                "Error: ". error_get_last()['message']
            );
        }

        return (string)$data;
    }
}