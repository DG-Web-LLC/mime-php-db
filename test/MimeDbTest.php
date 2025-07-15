<?php
/**
 * 
 */
namespace DGWebLLC\MimePhpDb\Test;

use DGWebLLC\MimePhpDb\MimeDb;
use PHPUnit\Framework\TestCase;

final class MimeDbTest extends TestCase {
    public function testIndexAccess(): void {
        $db = new MimeDb();

        $mime = $db['application/java-archive'];

        $this->assertEquals(
            'application/java-archive',
            $mime->name,
            'Verifies the object retrieved is for media type application/java-archive'
        );
    }
    public function testIteration(): void {
        $db = new MimeDb();
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

    public function testFilter(): void {
        $db = new MimeDb();
        $mime = [];

        $mime = $db->filter(function ($m) {
            return $m->name == 'application/java-archive';
        });

        print_r($mime);

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