<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Exception\Fetch\HttpFetchError;
use DGWebLLC\MimePhpDb\Exception\Fetch\ParseError;
use DGWebLLC\MimePhpDb\Fetch\AbstractClass;
use Composer\IO\IOInterface;
use DGWebLLC\MimePhpDb\Mime;

class Nginx extends AbstractClass {
    public function __construct(IOInterface|ConsoleIO|null $io = null) {
        parent::__construct("nginx", $io);
    }
    /**
     * URL for the mime.types file in the NGINX project source.
     * 
     * @var string
     */
    const DATASOURCE_URL = "https://raw.githubusercontent.com/nginx/nginx/master/conf/mime.types";
    /**
     * Each Mime Type entry in the nginx mime.types file follows the form: <br>
     *      type  [ext,];
     * 
     * This expression is broken into multiple parts: <br>
     *      '^'                     beginning of the line
     *      '(?:\s+)?'              checks for a beginning indent it's presence is optional
     *      '([\w-]+\/[\w+.-]+)'    checks for and captures a mime type code of "word/word" including the "+" character
     *      '(?:\s+)?'              checks for any whitespace character as few times as possible
     *      '((?:[ \w-]+)*)'        checks for and captures the extension(s) if found
     *      ';'                     checks that the character ";" terminates the line
     *      '$'                     end of the line 
     * @var string
     */
    const LINE_REGEX = '/^(?:\s+)?([\w-]+\/[\w+.-]+)(?:\s+)((?:[ \w-]+)*);$/m';
    public function fetch() {
        $this->_io->write("\nFetching NGINX Media Type data from ".self::DATASOURCE_URL."\n");

        $matches = null;
        $data = $this->httpRequest(self::DATASOURCE_URL);

        $matchLen = preg_match_all(self::LINE_REGEX, $data, $matches, PREG_SET_ORDER);

        if ($matchLen == 0) {
            throw new ParseError("Unable to parse nginx mime.types file; data may be corrupted or unavailable");
        }

        $i = 1;
        foreach ($matches as $match) {
            $mime = [
                "name" => $match[1],
                "extensions" => explode(" ", $match[2]),
                "source" => [$this->name]
            ];

            $this->addMime(new Mime($mime));

            $this->_io->write(sprintf("\rProcessing entry: [%04d of %04d]", $i, $matchLen), false);
            $i++;
        }

        $this->_io->write("\nNGINX Fetch Complete\n");
    }
}