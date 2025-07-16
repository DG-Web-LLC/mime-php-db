<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Test;

use DGWebLLC\MimePhpDb\Config;
use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Fetch\Custom;
use DGWebLLC\MimePhpDb\Fetch\Iana;
use DGWebLLC\MimePhpDb\Fetch\Nginx;
use DGWebLLC\MimePhpDb\Fetch\Apache;
use PHPUnit\Framework\TestCase;

final class FetchTest extends TestCase {
    const DATA_DIR = __DIR__.DIRECTORY_SEPARATOR."data";
    public static function tearDownAfterClass(): void {
        // Removes temporary files for next set of unit tests

        $files = glob(self::DATA_DIR.DIRECTORY_SEPARATOR."*");

        foreach ($files as $file) {
            $fname = basename($file);

            if ($fname != ".gitignore")
                unlink($file);
        }
    }
    public function testIana(): void {
        $obj = new Iana();

        $obj->fetch();
        $obj->save(self::DATA_DIR);

        $this->assertFileExists(
            self::DATA_DIR.DIRECTORY_SEPARATOR."iana",
            "Iana failed to create data source file"
        );
    }
    public function testNginx(): void {
        $obj = new Nginx();

        $obj->fetch();
        $obj->save(self::DATA_DIR);

        $this->assertFileExists(
            self::DATA_DIR.DIRECTORY_SEPARATOR."nginx",
            "Nginx failed to create data source file"
        );
    }
    public function testApache(): void {
        $obj = new Apache();

        $obj->fetch();
        $obj->save(self::DATA_DIR);

        $this->assertFileExists(
            self::DATA_DIR.DIRECTORY_SEPARATOR."apache",
            "Apache failed to create data source file"
        );
    }
    public function testCustom(): void {
        $obj = new Custom();

        $obj->fetch();
        $obj->save(self::DATA_DIR);

        $this->assertFileExists(
            self::DATA_DIR.DIRECTORY_SEPARATOR."custom",
            "Custom failed to create data source file"
        );
    }
}