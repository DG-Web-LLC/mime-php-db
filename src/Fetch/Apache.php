<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\Exception\Fetch\HttpFetchError;
use DGWebLLC\MimePhpDb\Exception\Fetch\ParseError;
use DGWebLLC\MimePhpDb\Fetch\AbstractClass;
use Composer\IO\IOInterface;

/**
 * Summary of Apache
 */
class Apache extends AbstractClass {
    public function __construct(IOInterface|null $io = null) {
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
        $data = file_get_contents(self::DATASOURCE_URL, false, $this->_context);
        
        if (!$data) {
           throw new HttpFetchError("HTTP Resource Unreachable: ".self::DATASOURCE_URL."\nError: ".error_get_last()['message']);
        }

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

            $this->_data['by-name'][$mime['name']] = $mime;

            foreach ($mime['extensions'] as $ext) {
                if ( isset($this->_data['by-extension'][$ext]) )
                    $this->_data['by-extension'][$ext][] = $mime;
                else
                    $this->_data['by-extension'][$ext] = [$mime];
            }
            
            $this->_io->write(sprintf("\rProcessing entry: [%04d of %04d]", $i, $matchLen), false);
            $i++;
        }
        
        $this->_io->write("\nApache Fetch Complete\n");
    }
}
