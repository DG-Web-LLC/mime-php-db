<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Exception\Fetch\ParseError;
use DGWebLLC\MimePhpDb\Fetch\AbstractClass;
use Composer\IO\IOInterface;
use DGWebLLC\MimePhpDb\Mime;

/**
 * Summary of Apache
 */
class Apache extends AbstractClass {
    public function __construct(IOInterface|ConsoleIO|null $io = null) {
        parent::__construct("apache", $io);
    }
    /**
     * URL for the mime.types file in the Apache HTTPD project source
     * @var string
     */
    const DATASOURCE_URL = "https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types";
    /**
     * Each Mime Type entry in the apache mime.types file follows the form: <br>
     *      [#] type  [ext,]
     * 
     * This expression is broken into multiple parts: <br>
     *      '^'                     beginning of the line
     *      '(?:# )?'              checks for a comment character "#" it's presence is optional
     *      '([\w-]+\/[\w+.-]+)'    checks for and captures a mime type code of "word/word" including the "+" character
     *      '(?:\s+)?'              checks for any whitespace character as few times as possible
     *      '((?:[ \w-]+)*)'        checks for and captures the extension(s) if found
     *      '$'                     end of the line 
     * @var string
     */
    const LINE_REGEX = '/^(?:# )?([\w-]+\/[\w+.-]+)(?:\s+)?((?:[ \w-]+)*)$/m';

    public function fetch() {
        $this->_io->write("\nFetching Apache Media Type data from ".self::DATASOURCE_URL."\n");

        $matches = null;
        $data = $this->httpRequest(self::DATASOURCE_URL);

        $matchLen = preg_match_all(self::LINE_REGEX, $data, $matches, PREG_SET_ORDER);

        if ($matchLen == 0) {
            throw new ParseError("Unable to parse apache mime.types file; data may be corrupted or unavailable");
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
        
        $this->_io->write("\nApache Fetch Complete\n");
    }
}
