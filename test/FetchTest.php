<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Test;

use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Fetch\Custom;
use DGWebLLC\MimePhpDb\Fetch\Iana;
use DGWebLLC\MimePhpDb\Fetch\Nginx;
use DGWebLLC\MimePhpDb\Fetch\Apache;
use PHPUnit\Framework\TestCase;

final class FetchTest extends TestCase {
    public function testIana(): void {
        $obj = new Iana();

        $obj->fetch();
        $obj->save();
    }
    public function testNginx(): void {
        $obj = new Nginx();

        $obj->fetch();
        $obj->save();
    }
    public function testApache(): void {
        $obj = new Apache();

        $obj->fetch();
        $obj->save();
    }
    public function testCustom(): void {
        $obj = new Custom();

        $obj->fetch();
        $obj->save();
    }
}