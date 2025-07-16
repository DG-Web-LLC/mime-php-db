<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Fetch;

use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Fetch\AbstractClass;
use Composer\IO\IOInterface;
use DGWebLLC\MimePhpDb\Mime;

/**
 * This class establishes know media types that are either missing for the available datasources or current
 * un-parsable using the current parsing logic.
 */
class Custom extends AbstractClass {
    public function __construct(IOInterface|ConsoleIO|null $io = null) {
        parent::__construct("custom", $io);
    }

    public function fetch() {
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/smil+xml",
            "extensions" => ["smil", "smi", "sml"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/smil+xml",
            "extensions" => ["smil", "smi", "sml"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/vnd.grafeq",
            "extensions" => ["gqf", "gqs"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/vnd.nitf",
            "extensions" => ["ntf", "nitf"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/vnd.solent.sdkm+xml",
            "extensions" => ["sdkm", "sdkd"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/vnd.visio",
            "extensions" => ["vsd", "vst", "vsw", "vss"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "application/vnd.zul",
            "extensions" => ["zir", "zirz"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "audio/l16",
            "extensions" => ["wav", "l16"],
        ]));
        $this->addMime(new Mime([
            "source" => [$this->name],
            "name" => "message/global-headers",
            "extensions" => ["u8hdr"],
        ]));
    }
}