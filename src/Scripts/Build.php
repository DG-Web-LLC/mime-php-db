<?php
namespace DGWebLLC\MimePhpDb\Scripts;

use Composer\Script\Event;
use DGWebLLC\MimePhpDb\Config;
use DGWebLLC\MimePhpDb\Exception\Build\FileWriteError;
use DGWebLLC\MimePhpDb\Exception\Build\DirectoryNotFound;
use DGWebLLC\MimePhpDb\Fetch\Apache;
use DGWebLLC\MimePhpDb\Fetch\Custom;
use DGWebLLC\MimePhpDb\Fetch\Iana;
use DGWebLLC\MimePhpDb\Fetch\Nginx;
use DGWebLLC\MimePhpDb\Mime;

class Build {
    public static function start(Event $e): void {
        $vendor = $e->getComposer()->getConfig()->get('vendor-dir');
        require_once $vendor.DIRECTORY_SEPARATOR.'autoload.php';

        $io = $e->getIO();

        // Asks the user for confirmation only if interactive mode is enabled.
        $update = $io->askConfirmation(
            "\nUpdate the mime-db datasource?\nPlease note that this process may take a few minutes to complete. Do you wish to proceed? [y/n]: ",
            !$io->isInteractive()
        );

        if ( !file_exists(Config::DATA_DIR.DIRECTORY_SEPARATOR.".") )
            throw new DirectoryNotFound("Data Directory Not Found");

        if ($update) {
            self::fetchDataSources($e);
            $io->write("\nData Source Update Complete\n");
        } else {
            $io->write("\nDatasource Update Aborted\n");
        }
    }
    public static function fetchDataSources(Event $e) {
        $io = $e->getIO();

        $io->write("Starting data source scape. . .");

        $sources = [
            new Apache($io), new Iana($io), new Nginx($io), new Custom($io)
        ];
        $sourceFiles = [];
        
        foreach ($sources as $source) {
            $source->fetch();
            $sourceFiles[] = $source->save(Config::DATA_DIR);
        }

        self::combineDataSources($e, $sourceFiles);
    }
    private static function combineDataSources(Event $e, array $sources): void {
        $io = $e->getIO();

        $io->write("\nCombining Data Sources . . .\n");
        $data = [];

        foreach ($sources as $source) {
            $contents = file_get_contents($source);
            $rows = explode("\n", $contents);

            foreach ($rows as $row) {
                $mime = new Mime($row);
                if ( !isset($data[$mime->name]) ) {
                    $data[$mime->name] = $mime;
                } else {
                    $data[$mime->name]->merge($mime);
                }
            }
        }

        $file = Config::DATA_DIR.DIRECTORY_SEPARATOR."data";
        $io->write("Writing data source to file: $file");
        $result = file_put_contents($file, implode("\n", $data));

        if ( $result === false ) {
            throw new FileWriteError("Could Not Write File: {$file}");
        }

        $io->write("\nData Source Combination Complete\n");
    }
}