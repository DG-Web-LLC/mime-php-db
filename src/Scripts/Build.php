<?php
namespace DGWebLLC\MimePhpDb\Scripts;

use Composer\Script\Event;
use Composer\IO\IOInterface;
use DGWebLLC\MimePhpDb\Config;
use DGWebLLC\MimePhpDb\ConsoleIO;
use DGWebLLC\MimePhpDb\Exception\Build\FileWriteError;
use DGWebLLC\MimePhpDb\Exception\Build\DirectoryNotFound;
use DGWebLLC\MimePhpDb\Fetch\Apache;
use DGWebLLC\MimePhpDb\Fetch\Custom;
use DGWebLLC\MimePhpDb\Fetch\Iana;
use DGWebLLC\MimePhpDb\Fetch\Nginx;
use DGWebLLC\MimePhpDb\Mime;

class Build {
    /**
     * Composer entry point, designed to be called a composer script event.
     * 
     * @param \Composer\Script\Event $e
     * @throws \DGWebLLC\MimePhpDb\Exception\Build\DirectoryNotFound
     * @return void
     */
    public static function start(Event $e): void {
        $vendor = $e->getComposer()->getConfig()->get('vendor-dir');
        require_once $vendor.DIRECTORY_SEPARATOR.'autoload.php';

        if ( !file_exists(Config::DATA_DIR.DIRECTORY_SEPARATOR.".") )
            throw new DirectoryNotFound("Data Directory Not Found");

        $io = $e->getIO();
        $interactive = $io->isInteractive();

        self::buildDataSource($io, $interactive);
    }
    /**
     * Direct access entry point, designed to be called from user PHP code.
     * 
     * @param \Composer\IO\IOInterface|\DGWebLLC\MimePhpDb\ConsoleIO|null $io
     * @param bool $interactive
     * @param string $dir
     * @return void
     */
    public static function buildDataSource(IOInterface|ConsoleIO|null $io, bool $interactive = false, string $dir = Config::DATA_DIR): void {
        if ($io == null) {
            $io = new ConsoleIO();
        }
        
        if ($interactive) {
            $update = $io->askConfirmation(
                "\nUpdate the mime-db datasource?\nPlease note that this process may take a few minutes to complete. Do you wish to proceed? [y/n]: ",
                false
            );
        } else {
            $update = true;
        }

        if (!$update) {
            $io->write("\nDatasource Update Aborted\n");
        } else {
            self::fetchDataSources($io, $dir);
            $io->write("\nData Source Update Complete\n");
        }
    }
    private static function fetchDataSources(IOInterface|ConsoleIO $io, string $dir): void {
        $io->write("Starting data source scape. . .");

        $sources = [
            new Apache($io), new Iana($io), new Nginx($io), new Custom($io)
        ];
        $sourceFiles = [];
        
        foreach ($sources as $source) {
            $source->fetch();
            $sourceFiles[] = $source->save($dir);
        }

        self::combineDataSources($io, $sourceFiles, $dir);
    }
    private static function combineDataSources(IOInterface|ConsoleIO $io, array $sources, string $dir): void {
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

        $file = $dir.DIRECTORY_SEPARATOR."data";
        $io->write("Writing data source to file: $file");
        $result = file_put_contents($file, implode("\n", $data));

        if ( $result === false ) {
            throw new FileWriteError("Could Not Write File: {$file}");
        }

        $io->write("\nData Source Combination Complete\n");
    }
}