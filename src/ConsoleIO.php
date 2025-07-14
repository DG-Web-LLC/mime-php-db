<?php
/**
 * 
 */

namespace DGWebLLC\MimePhpDb;

use Composer\IO\IOInterface;

/**
 * This class is designed to provide a common console point, working with Composer/IO,
 * PHPUnit test console, and the standard php output console.
 */
class ConsoleIO {
    private $_composerIO = null;
    private $_output;
    private $_error;
    private $_input;
    public function __construct(IOInterface|null $io = null) {
        $this->_composerIO = $io;
        $this->_output = fopen("php://stdout", 'w');
        $this->_error = fopen("php://stderr", 'w');
        $this->_input = fopen("php://stdin", 'r');
    }
    public function __destruct() {
        fclose($this->_output);
        fclose($this->_error);
        fclose($this->_input);
    }
    /**
     * Summary of write
     * @param string|string[] $messages
     * @param bool $newline
     * @return void
     */
    public function write($messages, bool $newline = true): void {
        if ($this->_composerIO != null) {
            $this->_composerIO->write($messages, $newline);
        } else {
            $data = implode(
                $newline? "\n": "",
                is_array($messages)? $messages: [$messages]
            ).($newline? "\n": "");

            fwrite($this->_output, $data);
        }
    }
    /**
     * Summary of writeError
     * @param string|string[] $messages
     * @param bool $newline
     * @return void
     */
    public function writeError($messages, bool $newline = true): void {
        if ($this->_composerIO != null) {
            $this->_composerIO->writeError($messages, $newline);
        } else {
            $data = implode(
                $newline? "\n": "",
                is_array($messages)? $messages: [$messages]
            ).$newline? "\n": "";

            fwrite($this->_error, $data);
        }
    }
    /**
     * Summary of ask
     * @param string $question
     * @param mixed $default
     * @return string
     */
    public function ask(string $question, $default = null): string {
        $out = $default;

        if ($this->_composerIO != null) {
            $out = (string)$this->_composerIO->ask($question, $default);
        } else {
            $this->write($question, false);
            $rs = fgets($this->_input, 4096);
            $out = $rs;
        }

        return $out;
    }
    /**
     * Summary of askConfirmation
     * @param string $question
     * @param bool $default
     * @return void
     */
    public function askConfirmation(string $question, bool $default = true): bool {
        $out = $default;

        if ($this->_composerIO != null) {
            $out = $this->_composerIO->askConfirmation($question, $default);
        } else {
            $this->write($question, false);
            $rs = strtolower(trim(fgets($this->_input, 4096)));
            $out = ($rs == 'y' || $rs == 'yes');
        }

        return $out;
    }
}