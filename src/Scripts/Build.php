<?php
namespace DGWebLLC\MimePhpDb\Scripts;

use Composer\Script\Event;
use DGWebLLC\MimePhpDb\Exception\Fetch\FileWriteError;
use DGWebLLC\MimePhpDb\Fetch\Apache;
use DGWebLLC\MimePhpDb\Fetch\Custom;
use DGWebLLC\MimePhpDb\Fetch\Iana;
use DGWebLLC\MimePhpDb\Fetch\Nginx;

class Build {
    public static function start(Event $e): void {
        $io = $e->getIO();

        $update = $io->askConfirmation(
            "\nUpdate the mime-db datasource?\nPlease note that this process may take a few minutes to complete. Do you wish to proceed? [y/n]: ",
            false
        );

        if ($update)
            self::fetchDataSources($e);
    }
    const DATA_DIR = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."data";
    public static function fetchDataSources(Event $e) {
        $io = $e->getIO();

        $io->write("Starting data source scape. . .");

        $sources = [
            new Apache($io), new Iana($io), new Nginx($io), new Custom($io)
        ];
        $sourceFiles = [];
        
        foreach ($sources as $source) {
            $source->fetch();
            $sourceFiles[] = $source->save(self::DATA_DIR);
        }

        self::combineDataSources($e, $sourceFiles);
    }
    private static function combineDataSources(Event $e, array $sources): void {
        $io = $e->getIO();
        $data = ['by-name' => [], 'by-extension' => []];

        $io->write("\nCombining Data Sources . . .\n");

        foreach ($sources as $source) {
            $tmp = require($source);

            foreach ($tmp['by-name'] as $entry) {
                if ( isset($data['by-name'][$entry['name']]) ) {
                    $data['by-name'][$entry['name']]['extensions'] = array_values(
                        array_unique(
                            array_merge(
                                $data['by-name'][$entry['name']]['extensions'] ?? [],
                                $entry['extensions'] ?? []
                            )
                        )
                    );
                    $data['by-name'][$entry['name']]['source'] = array_values(
                        array_unique(
                            array_merge(
                                $data['by-name'][$entry['name']]['source'] ?? [],
                                $entry['source'] ?? []
                            )
                        )
                    );
                } else {
                    $data['by-name'][$entry['name']] = $entry;
                }
            }
        }

        foreach ($data['by-name'] as $entry) {
            foreach ($entry['extensions'] as $ext) {
                if ( isset($data['by-extension'][$ext]) )
                    $data['by-extension'][$ext] = [$entry];
                else
                    $data['by-extension'][$ext][] = $entry;
            }
        }

        $file = self::DATA_DIR.DIRECTORY_SEPARATOR."data";
        $io->write("Writing data source to file: $file");
        $result = file_put_contents($file, "<?php\nreturn ".(var_export($data, true)).";\n");

        if ( $result === false ) {
            throw new FileWriteError("Could Not Write File: {$file}");
        }

        $io->write("\nData Source Build Complete\n");
    }
}