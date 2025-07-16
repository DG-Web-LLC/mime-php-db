<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Test;

use DGWebLLC\MimePhpDb\MimeDb;
use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Scripts\Build;
use PHPUnit\Framework\TestCase;

final class MimeDbTest extends TestCase {
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
    public function testBuild(): void {
        Build::buildDataSource(new ConsoleIO(), false, self::DATA_DIR);

        $this->assertFileExists(self::DATA_DIR.DIRECTORY_SEPARATOR."data", 'Failed to create data source');
    }
    /**
     * @depends testBuild
     */
    public function testIndexAccess(): void {
        $db = new MimeDb(self::DATA_DIR.DIRECTORY_SEPARATOR."data");

        $mime = $db['application/java-archive'];

        $this->assertEquals(
            'application/java-archive',
            $mime->name,
            'Verifies the object retrieved is for media type application/java-archive'
        );
    }
    /**
     * @depends testBuild
     */
    public function testIteration(): void {
        $db = new MimeDb(self::DATA_DIR.DIRECTORY_SEPARATOR."data");
        $mime = null;

        foreach ($db as $mimeType) {
            if ($mimeType->name == 'application/java-archive') {
                $mime = $mimeType;
                break;
            }
        }

        $this->assertNotNull($mime, 'Verifies media type was found');
        $this->assertEquals(
            'application/java-archive',
            $mime->name,
            'Verifies the object retrieved is for media type application/java-archive'
        );
    }
    /**
     * @depends testBuild
     */
    public function testFilter(): void {
        $db = new MimeDb(self::DATA_DIR.DIRECTORY_SEPARATOR."data");
        $mime = [];

        $mime = $db->filter(function ($m) {
            return $m->name == 'application/java-archive';
        });

        $this->assertNotEmpty($mime, 'Verifies that at least one match was found');
        $this->assertEquals(
            count($mime),
            1,
            'Verifies that a single match was found'
        );
        $this->assertTrue(
            array_key_exists('application/java-archive', $mime),
            'Verifies the object retrieved is for media type application/java-archive'
        );
    }
}