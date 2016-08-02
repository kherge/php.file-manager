<?php

namespace Test\KHerGe\File;

use KHerGe\File\File;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the file manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\File\File
 */
class FileTest extends TestCase
{
    /**
     * The path to the file.
     *
     * @var string
     */
    private $file;

    /**
     * The file manager.
     *
     * @var File
     */
    private $manager;

    /**
     * Verify that the file is used by the stream manager.
     */
    public function testUsingAFileStream()
    {
        $this->manager->write('test');
        $this->manager->release();

        self::assertEquals(
            'test',
            file_get_contents($this->file),
            'The file was not used by the stream manager.'
        );
    }

    /**
     * Creates a new file manager.
     */
    protected function setUp()
    {
        $this->file = tempnam(sys_get_temp_dir(), 'php-');
        $this->manager = new File($this->file, 'w+');
    }

    /**
     * Deletes the temporary file.
     */
    protected function tearDown()
    {
        unlink($this->file);
    }
}
