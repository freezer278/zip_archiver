<?php

namespace Vmorozov\ZipArchiver\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vmorozov\ZipArchiver\ZipArchiver;
use ZipArchive;

class ZipArchiverTest extends TestCase
{
    /**
     * @var ZipArchiver
     */
    private $archiver;
    /**
     * @var string
     */
    private $tempStoragePath;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->archiver = new ZipArchiver();
        $this->tempStoragePath = __DIR__ . '/test-storage';
    }

    /**
     *
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        shell_exec('rm -r ' . $this->tempStoragePath . '/*');
    }

    /**
     *
     */
    public function testCreateFromFolder(): void
    {
        $archiveLocation = $this->tempStoragePath . '/archive_' . $this->getRandomString() . '.zip';
        $archiveContentsLocation = $this->tempStoragePath . '/archive_contents_' . $this->getRandomString();

        $folder1Path = '/folder1';
        $folder2Path = "$folder1Path/folder2";
        $file1Path = '/file1.txt';
        $file2Path = "$folder1Path/file2.txt";
        $file3Path = "$folder2Path/file3.txt";

        @mkdir($archiveContentsLocation . $folder2Path, 0777, true);
        file_put_contents($archiveContentsLocation . $file1Path, $file1Path);
        file_put_contents($archiveContentsLocation . $file2Path, $file2Path);
        file_put_contents($archiveContentsLocation . $file3Path, $file3Path);
        $this->archiver->createFromFolder($archiveContentsLocation, $archiveLocation);

        $zip = new ZipArchive();
        if ($zip->open($archiveLocation) !== true) {
            throw new RuntimeException('zip extraction failed');
        }

        $this->assertEquals($file1Path, $zip->getFromName(ltrim($file1Path, '/')));
        $this->assertEquals($file2Path, $zip->getFromName($file2Path));
        $this->assertEquals($file3Path, $zip->getFromName($file3Path));
    }

    /**
     *
     */
    public function testExtractTo(): void
    {
        $archiveLocation = $this->tempStoragePath . '/archive_' . $this->getRandomString() . '.zip';
        $archiveContentsLocation = $this->tempStoragePath . '/archive_contents_' . $this->getRandomString();
        $extractedArchiveContentsLocation = $this->tempStoragePath . '/extracted_archive_contents_' . $this->getRandomString();

        $folder1Path = '/folder1';
        $folder2Path = "$folder1Path/folder2";
        $file1Path = '/file1.txt';
        $file2Path = "$folder1Path/file2.txt";
        $file3Path = "$folder2Path/file3.txt";

        @mkdir($archiveContentsLocation . $folder2Path, 0777, true);
        file_put_contents($archiveContentsLocation . $file1Path, $file1Path);
        file_put_contents($archiveContentsLocation . $file2Path, $file2Path);
        file_put_contents($archiveContentsLocation . $file3Path, $file3Path);
        $this->archiver->createFromFolder($archiveContentsLocation, $archiveLocation);

        $this->archiver->extractTo($archiveLocation, $extractedArchiveContentsLocation);

        $this->assertTrue(file_exists($extractedArchiveContentsLocation . $file1Path));
        $this->assertTrue(file_exists($extractedArchiveContentsLocation . $file2Path));
        $this->assertTrue(file_exists($extractedArchiveContentsLocation . $file3Path));

        $this->assertEquals($file1Path, file_get_contents($extractedArchiveContentsLocation . $file1Path));
        $this->assertEquals($file2Path, file_get_contents($extractedArchiveContentsLocation . $file2Path));
        $this->assertEquals($file3Path, file_get_contents($extractedArchiveContentsLocation . $file3Path));
    }

    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    private function getRandomString(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $result;
    }
}
