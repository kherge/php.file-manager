<?php

namespace Test\KHerGe\File;

use KHerGe\File\Exception\LockException;
use KHerGe\File\Stream;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the file stream manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\File\Stream
 */
class StreamTest extends TestCase
{
    /**
     * The file stream manager.
     *
     * @var Stream
     */
    private $manager;

    /**
     * The file stream.
     *
     * @var resource
     */
    private $stream;

    /**
     * Verify that the end of the file can be checked.
     */
    public function testCheckForTheEndOfTheFile()
    {
        fwrite($this->stream, 'test');
        fseek($this->stream, 0);

        self::assertFalse(
            $this->manager->eof(),
            'The cursor should not be at the end of the file.'
        );

        fseek($this->stream, 4);

        self::assertTrue(
            $this->manager->eof(),
            'The cursor should be at the end of the file.'
        );
    }

    /**
     * Verify that the contents of the file can be iterated.
     */
    public function testIterateThroughTheFile()
    {
        $contents = file_get_contents(__FILE__);

        fwrite($this->stream, $contents);
        fseek($this->stream, 0);

        self::assertEquals(
            $contents,
            join('', iterator_to_array($this->manager->iterate())),
            'The file was not iterated properly.'
        );

        fseek($this->stream, 0);

        self::assertEquals(
            substr($contents, 0, 10),
            join('', iterator_to_array($this->manager->iterate(10))),
            'The file was not iterated properly.'
        );
    }

    /**
     * Verify that the stream can be locked.
     */
    public function testLockTheStream()
    {
        $file = tempnam(sys_get_temp_dir(), 'fm-');
        $manager = new Stream(fopen($file, 'c'));

        $manager->lock(true, true);

        return [$file, $manager];
    }

    /**
     * @depends testLockTheStream
     *
     * Verify that acquiring a second lock throws an exception.
     *
     * @param string|Stream[] $args The test case dependencies.
     */
    public function testLockingAgainThrowsAnException(array $args)
    {
        $manager = new Stream(fopen($args[0], 'c'));

        $this->expectException(LockException::class);

        $manager->lock(true, true);
    }

    /**
     * Verify that the contents of the file can be read.
     */
    public function testReadFromTheFile()
    {
        fwrite($this->stream, 'test');
        fseek($this->stream, 0);

        self::assertEquals(
            'test',
            $this->manager->read(),
            'The file was not read properly.'
        );

        fseek($this->stream, 0);

        self::assertEquals(
            'te',
            $this->manager->read(2),
            'The file was not read properly.'
        );
    }

    /**
     * Verify that the internal cursor can be moved.
     */
    public function testMoveInternalCursor()
    {
        fwrite($this->stream, 'test');

        $this->manager->seek(2);

        self::assertEquals(
            2,
            ftell($this->stream),
            'The internal cursor was not moved properly.'
        );
    }

    /**
     * Verify that the size of the file can be determined.
     */
    public function testGetTheSizeOfTheFile()
    {
        fwrite($this->stream, file_get_contents(__FILE__));

        self::assertEquals(
            filesize(__FILE__),
            $this->manager->size(),
            'The size of the file was not determined properly.'
        );
    }

    /**
     * Verify that a source file can be streamed to a target file.
     */
    public function testStreamSourceToTarget()
    {
        $source = new Stream(fopen(__FILE__, 'r'));

        $this->manager->stream($source);

        fseek($this->stream, 0);

        $buffer = '';

        do {
            $buffer .= fread($this->stream, 8096);
        } while (!feof($this->stream));

        self::assertEquals(
            $buffer,
            file_get_contents(__FILE__),
            'The file contents were not streamed properly.'
        );
    }

    /**
     * Verify that the internal cursor position can be determined.
     */
    public function testDetermineInternalCursorPosition()
    {
        fwrite($this->stream, 'test');
        fseek($this->stream, 2);

        self::assertEquals(
            2,
            $this->manager->tell(),
            'The internal cursor position was not determined properly.'
        );
    }

    /**
     * @depends testLockTheStream
     *
     * Verify that the lock can be released.
     *
     * @param string|Stream[] $args The test case dependencies.
     */
    public function testUnlockTheStream(array $args)
    {
        $args[1]->unlock();

        $manager = new Stream(fopen($args[0], 'c'));
        $manager->lock(true, true);
        $manager->unlock();

        unset($manager);
        unlink($args[0]);
    }

    /**
     * Verify that the contents of the file can be written.
     */
    public function testWriteTheFileContents()
    {
        $this->manager->write('test');

        fseek($this->stream, 0);

        self::assertEquals(
            'test',
            fread($this->stream, 4),
            'The contents of the file were not written properly.'
        );
    }

    /**
     * Creates a new file stream manager.
     */
    protected function setUp()
    {
        $this->stream = fopen('php://memory', 'w+');
        $this->manager = new Stream($this->stream);
    }
}
