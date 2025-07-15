<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Fetch;

use \SimpleXMLElement;
use DGWebLLC\MimePhpDb\Mime;
use DGWebLLC\MimePhpDb\Exception\Fetch\ParseError;
use DGWebLLC\MimePhpDb\Fetch\AbstractClass;
use Composer\IO\IOInterface;

class Iana extends AbstractClass {
    public function __construct(IOInterface|null $io = null) {
        parent::__construct("iana", $io);
    }
    /**
     * URL for the Iana Media Types File
     * 
     * @var string
     */
    const DATASOURCE_URL = "https://www.iana.org/assignments/media-types";
    /**
     * Summary of EXTENSION_LINE
     * @var string
     */
    const EXTENSION_LINE = '/^(?:\s*(?:\d*\.\s+)?)file extension\(?s?\)?\s*?:\s*(.+(?:\s^.*?(?<=[\s\'"])\.[\S]+?(?=\.?$))?)/mi';
    /**
     * Summary of EXTENSION_EXTRACT
     * @var string
     */
    const EXTENSION_EXTRACT = '/("|\')(?:\.)?([\w.-]+)(\1)|(?:\.)([\w.-]+)|^([\w.-]+)$/mi';
    /**
     * Summary of URL_FILTER
     * @var string
     */
    const URL_FILTER = '/(?:(?:https?:\/\/)|(?:www\.))[-a-zA-Z0-9@:%._\+~#=]{1,256}/i';
    /**
     * Summary of NOISE_FILTER
     * @var string
     */
    const NOISE_FILTER = '/\(.+?(?:\)|$)|(?:are\sboth|where|documents\sare|is\sdeclared).*$|#|:/im';
    /**
     * List of words and phrases that indicate a media type does not have an extension
     * @var array
     */
    const EXCLUSION_LIST = [
        "n/a",
        "none",
        "uses the mime",
        "uses the media",
        "does not require",
        "not expected",
        "unknown",
        "not applicable",
        "not required",
        "does not propose",
        "not available",
        "file type code",
        "none defined",
        "no specific file extension",
        "undefined",
        "do not apply",
        "does not apply",
        "not designed yet",
        "are not"
    ];
    public function fetch() {
        $this->_io->write("\nFetching Iana Media Type data from ".self::DATASOURCE_URL."\n");

        $xml = $this->fetchBaseXML();
        $progress = [
            "registry" => [
                "pos" => 1,
                "total" => count($xml->registry),
            ],
            "record" => [
                "pos" => 0,
                "total" => 0,
            ],
        ];
        $i = 0;

        foreach ($xml->registry as $registry) {
            $progress['record']['pos'] = 1;
            $progress['record']['total'] = count($registry->record);

            foreach ($registry->record as $record) {
                $entry = $this->fetchTemplateMime($record->file);

                $this->addMime(new Mime($entry));
                
                $this->_io->write(
                    sprintf(
                        "\rChecking Registry: [%04d of %04d], Record [%04d of %04d]",
                        $progress['registry']['pos'], $progress['registry']['total'],
                        $progress['record']['pos'], $progress['record']['total']
                    ),
                    false
                );
                
                $progress['record']['pos']++;
                $i++;
            }
            $progress['registry']['pos']++;
        }

        $this->_io->write("\nParsed types: $i");
        $this->_io->write("Iana Fetch Complete\n");
    }
    
    private function fetchBaseXML(): bool|SimpleXMLElement {
        $data = $this->httpRequest(self::DATASOURCE_URL."/media-types.xml");

        $xml = simplexml_load_string($data);

        if ($xml === false) {
            throw new ParseError("Unable to parse apache mime.types file; data may be corrupted or unlivable");
        }

        return $xml;
    }
    private function fetchTemplateMime(string $name) {
        $data = $this->httpRequest(self::DATASOURCE_URL."/".$name);

        $mime = [
            "name" => $name,
            "source" => [$this->name],
            "extensions" => [],
        ];
        $exclude = true;
        $matches = null;
        $matchLen = preg_match(self::EXTENSION_LINE, $data, $matches);

        if ($matchLen !== false && $matchLen > 0) {
            $extLine = strtolower(trim(preg_replace('/\s+/', ' ', $matches[1])));

            foreach (self::EXCLUSION_LIST as $ex) {
                if (empty($extLine) || strpos($extLine, $ex) !== false) {
                    $exclude = true;
                    break;
                } else {
                    $exclude = false;
                }
            }
            
            if (!$exclude) {
                $extLine = preg_replace(self::URL_FILTER, "", $extLine);
                $matchLen = preg_match_all(self::EXTENSION_EXTRACT, $extLine, $matches, PREG_SET_ORDER);
            } else {
                $matchLen = false;
            }
        }

        if ($matchLen !== false && $matchLen > 0) {
            foreach ($matches as $match) {
                $mime['extensions'][] = ($match[2] ?? "").($match[4] ?? "").($match[5] ?? "");
            }
        } else if (!$exclude && $extLine != "") {
            $extLine = preg_replace(self::NOISE_FILTER, "", $extLine);
            $words = preg_split('/, ?|,? or |,? and /', $extLine);
            $isWords = false;
            

            for ($i = 0; $i < count($words); $i++) {
                $words[$i] = trim($words[$i]);

                if (!preg_match('/^[\w.-]+$/', $words[$i])) {
                    $isWords = false;
                    break;
                } else {
                    $isWords = true;
                }
            }

            if ($isWords) {
                $mime['extensions'] = $words;
            }
        }

        return $mime;
    }
}