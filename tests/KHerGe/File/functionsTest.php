<?php

namespace Test\KHerGe\File;

use KHerGe\File\Exception\PathException;
use PHPUnit_Framework_TestCase as TestCase;

use function KHerGe\File\remove;

/**
 * Verifies that the library functions function (hah!) as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class functionsTest extends TestCase
{
    /**
     * Verify that the a file path can be removed.
     *
     * @covers \KHerGe\File\remove
     */
    public function testRemoveAFilePath()
    {
        $file = tempnam(sys_get_temp_dir(), 'fm-');

        remove($file);

        self::assertFileNotExists(
            $file,
            'The file path was not removed.'
        );
    }

    /**
     * Verify that remove a non-existent file path throws an exception.
     *
     * @covers \KHerGe\File\remove
     */
    public function testRemovingANonExistentPathThrowsAnException()
    {
        $path = '/this/path/should/not/exist/i/am/sorry/if/it/does';

        $this->expectException(PathException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The path "%s" could not be removed.',
                $path
            )
        );

        @remove($path);
    }

    /**
     * Verify that a directory path can be removed.
     *
     * @covers \KHerGe\File\remove
     */
    public function testRemoveADirectoryPath()
    {
        $dir = tempnam(sys_get_temp_dir(), 'fm-');

        unlink($dir);
        mkdir($dir);
        touch($dir . '/test');

        remove($dir);

        self::assertFileNotExists(
            $dir,
            'The directory was not removed.'
        );
    }

    /**
     * Verify that symlink'd directory contents are not removed.
     *
     * @covers \KHerGe\File\remove
     */
    public function testRemoveADirectoryLinkWithoutDeletingContents()
    {
        $dir = tempnam(sys_get_temp_dir(), 'fm-');

        unlink($dir);
        mkdir($dir);
        touch($dir . '/test');

        $link = tempnam(sys_get_temp_dir(), 'fm-');

        unlink($link);
        symlink($dir, $link);

        remove($link);

        self::assertFileNotExists(
            $link,
            'The directory link was not removed.'
        );

        self::assertFileExists(
            $dir . '/test',
            'The linked directory was deleted too.'
        );
    }

    /**
     * Verify that symlink'd directory contents are also removed.
     *
     * @covers \KHerGe\File\remove
     */
    public function testRemoveADirectoryLinkAndTheLinkedDirectoryContents()
    {
        $dir = tempnam(sys_get_temp_dir(), 'fm-');

        unlink($dir);
        mkdir($dir);
        touch($dir . '/test');

        $link = tempnam(sys_get_temp_dir(), 'fm-');

        unlink($link);
        symlink($dir, $link);

        remove($link, true);

        self::assertFileNotExists(
            $link,
            'The directory link was not removed.'
        );

        self::assertFileNotExists(
            $dir . '/test',
            'The linked directory contents were not deleted.'
        );
    }
}
