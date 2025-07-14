<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\Fetch\AbstractClass;
use Composer\IO\IOInterface;

/**
 * This class establishes know media types that are either missing for the available datasources or current
 * un-parsable using the current parsing logic.
 */
class Custom extends AbstractClass {
    public function __construct(IOInterface|null $io = null) {
        parent::__construct("custom", $io);
    }

    public function fetch() {
        $this->_data['by-name'] = [
            "application/smil+xml" => [
                "source" => [$this->name],
                "name" => "application/smil+xml",
                "extensions" => ["smil", "smi", "sml"],
            ],
            "application/vnd.grafeq" => [
                "source" => [$this->name],
                "name" => "application/vnd.grafeq",
                "extensions" => ["gqf", "gqs"],
            ],
            "application/vnd.nitf" => [
                "source" => [$this->name],
                "name" => "application/vnd.nitf",
                "extensions" => ["ntf", "nitf"],
            ],
            "application/vnd.solent.sdkm+xml" => [
                "source" => [$this->name],
                "name" => "application/vnd.solent.sdkm+xml",
                "extensions" => ["sdkm", "sdkd"],
            ],
            "application/vnd.visio" => [
                "source" => [$this->name],
                "name" => "application/vnd.visio",
                "extensions" => ["vsd", "vst", "vsw", "vss"],
            ],
            "application/vnd.zul" => [
                "source" => [$this->name],
                "name" => "application/vnd.zul",
                "extensions" => ["zir", "zirz"],
            ],
            "audio/l16" => [
                "source" => [$this->name],
                "name" => "audio/l16",
                "extensions" => ["wav", "l16"],
            ],
            "message/global-headers" => [
                "source" => [$this->name],
                "name" => "message/global-headers",
                "extensions" => ["u8hdr"],
            ],
        ];

        foreach ($this->_data['by-name'] as $mime) {
            foreach ($mime['extensions'] as $ext) {
                if ( isset($this->_data['by-extension'][$ext]) )
                    $this->_data['by-extension'][$ext][] = $mime;
                else
                    $this->_data['by-extension'][$ext] = [$mime];
            }
        }
    }
}