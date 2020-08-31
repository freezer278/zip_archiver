<?php

namespace Vmorozov\ZipArchiver;

use Exception;
use RuntimeException;
use ZipArchive;

class ZipArchiver
{
    /**
     * @param string $folderPath
     * @param string $zipFilePath
     */
    public function createFromFolder(string $folderPath, string $zipFilePath): void
    {
        $this->fixTempFolderPresence($zipFilePath);

        $zipArchive = new ZipArchive();
        if (!$zipArchive->open($zipFilePath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
            throw new RuntimeException('Failed to create zip archive');
        }

        $this->addFilesToArchive($zipArchive, $folderPath);

        if (!$zipArchive->status === ZIPARCHIVE::ER_OK) {
            throw new RuntimeException('Failed to write local files to zip');
        }

        $zipArchive->close();
    }

    /**
     * @param string $zipFilePath
     */
    private function fixTempFolderPresence(string $zipFilePath): void
    {
        if (!file_exists($zipFilePath)) {
            $path = explode('/', $zipFilePath);
            array_pop($path);
            $path = implode('/', $path);

            if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
    }

    /**
     * @param ZipArchive $archive
     * @param string $path
     * @param string $directory
     */
    private function addFilesToArchive(ZipArchive $archive, string $path, string $directory = ''): void
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if (in_array($file, ['..', '.'])) {
                continue;
            }

            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->addFilesToArchive(
                    $archive,
                    $filePath,
                    str_replace('//', '/', $directory . str_replace($path, '', $filePath) . '/')
                );
            } else {
                $archive->addFile($filePath, $directory . $file);
            }
        }
    }

    /**
     * @param string $zipPath
     * @param string $destinationPath
     */
    public function extractTo(string $zipPath, string $destinationPath): void
    {
        $zip = new ZipArchive();
        $res = $zip->open($zipPath);
        if ($res !== true) {
            throw new RuntimeException('zip extraction failed. Code: ' . $res);
        }
        $zip->extractTo($destinationPath);
        $zip->close();
    }
}
