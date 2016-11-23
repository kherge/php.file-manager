<?php

namespace KHerGe\File;

use Generator;
use KHerGe\File\Exception\CursorException;
use KHerGe\File\Exception\LockException;
use KHerGe\File\Exception\ReadException;
use KHerGe\File\Exception\ResourceException;
use KHerGe\File\Exception\WriteException;

/**
 * Defines the public interface for a file manager.
 *
 * A file manager simplifies the handling of read and write operations by
 * throwing an exception when an operation fails. This is opposed to the way
 * file operations are typically handled in that the errors can slip through
 * unless the return values are checked.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface FileInterface
{
    /**
     * Indicates that the cursor movement is exact.
     *
     * @var integer
     */
    const EXACT = SEEK_SET;

    /**
     * Indicates that the cursor movement is relative.
     *
     * @var integer
     */
    const RELATIVE = SEEK_CUR;

    /**
     * Indicates that the cursor movement is relative to the end.
     *
     * @var integer
     */
    const RELATIVE_END = SEEK_END;

    /**
     * Releases the underlying resource if it is still available.
     *
     * The destructor, in addition to possibly other operations, will release
     * the underlying resource if it has not already been released. This will
     * allow for a clean exit and any last minute flushing of buffers.
     */
    public function __destruct();

    /**
     * Checks if the end of the file has been reached.
     *
     * ```php
     * do {
     *     // ...
     * } while (!$file->eof());
     * ```
     *
     * @return boolean Returns `true` if it has, or `false` if it has not.
     *
     * @throws CursorException If the end could not be checked.
     */
    public function eof();

    /**
     * Iterates through the contents of the file.
     *
     * The `iterate()` method will read enough bytes to fill the buffer before
     * yielding it. If a number of `$bytes` is specified to read, an exception
     * is thrown if the exact number of bytes could not be read. If a number of
     * `$bytes` is not specified, the file will be iterated through until the
     * end has been reached.
     *
     * ```php
     * foreach ($file->iterate() as $chunk) {
     *     // ...
     * }
     * ```
     *
     * @param integer $bytes  The number of bytes to read.
     * @param integer $buffer The number of bytes to yield.
     *
     * @return Generator|string[] The contents of the file.
     */
    public function iterate($bytes = 0, $buffer = 1024);

    /**
     * Attempts to acquire a portable advisory lock.
     *
     * The `lock()` method will check to see if locking is supported and then
     * attempt to create a lock by using `flock()`. If locking is not supported
     * or if a lock could not be acquired, an exception is thrown.
     *
     * ```php
     * $file->lock(true, true);
     * ```
     *
     * @param boolean $exclusive   Is the lock exclusive?
     * @param boolean $nonBlocking Is the lock non-blocking?
     *
     * @throws LockException If the lock could not be acquired.
     */
    public function lock($exclusive = false, $nonBlocking = false);

    /**
     * Reads the contents of the file.
     *
     * The `read()` method will read a number of `$bytes` from the file. If a
     * number of `$bytes` is not specified (i.e. is `0`), the file will be read
     * until the end has been reached. If a number of `$bytes` is specified, an
     * exception is thrown if the exact number of bytes could not be read.
     *
     * ```php
     * $chunk = $file->read(1024);
     * ```
     *
     * @param integer $bytes The number of bytes to read.
     *
     * @return string The contents read from the file.
     *
     * @throws ReadException If the file could not be read.
     */
    public function read($bytes = 0);

    /**
     * Releases the underlying resource.
     *
     * The releasing of a resource could be the closing of a stream or the
     * freeing of used memory. This method is made available in order to force
     * an early release in case object destruction comes too late in a process.
     *
     * ```php
     * $file->release();
     * ```
     *
     * @throws ResourceException If the resource could not be released.
     */
    public function release();

    /**
     * Moves the internal cursor.
     *
     * The `seek()` method will move the internal cursor to a `$position`
     * that is measured in bytes. The `$mode` determines how the cursor
     * should be moved.
     *
     * | Mode                          | Description                                        |
     * |:------------------------------|:---------------------------------------------------|
     * | `FileInterface::EXACT`        | Moves the cursor to an exact position.             |
     * | `FileInterface::RELATIVE`     | Moves the cursor relative to the current position. |
     * | `FileInterface::RELATIVE_END` | Moves the cursor relative to the end of the file.  |
     *
     * If using `EXACT`, the cursor must be greater than or equal to zero. If
     * using `RELATIVE`, the cursor may be greater, less than, or equal to zero.
     * If using `RELATIVE_END`, the cursor must be less than or equal to zero.
     *
     * ```php
     * $file->seek(-123, FileInterface::RELATIVE_END);
     * ```
     *
     * @param integer $position The position to move to.
     * @param integer $mode     The way to move the cursor.
     *
     * @throws CursorException If the cursor could not be moved.
     */
    public function seek($position, $mode = self::EXACT);

    /**
     * Reads the entire file to determine its size.
     *
     * The `size()` method will move the internal cursor to the beginning of
     * the file and count the number of bytes that are read until the end has
     * been reached.
     *
     * ```php
     * $size = $file->size();
     * ```
     *
     * > It may be important to note that "strange things" (TM) will happen
     * > on 32-bit platforms if the size of the file is greater than 2GB.
     *
     * @return integer The size of the file in bytes.
     *
     * @throws CursorException If the internal cursor could not be moved.
     * @throws ReadException   If the file could not be read.
     */
    public function size();

    /**
     * Streams the contents of a source file to this target file.
     *
     * @param FileInterface $file   The file to read from.
     * @param integer       $bytes  The number of bytes to read.
     * @param integer       $buffer The size of the read buffer.
     *
     * @throws ReadException  If the source file could not be read.
     * @throws WriteException If the target file could not be written.
     */
    public function stream(FileInterface $file, $bytes = 0, $buffer = 1024);

    /**
     * Determines the current position of the internal cursor.
     *
     * ```php
     * $position = $file->tell();
     * ```
     *
     * @return integer The exact current position.
     *
     * @throws CursorException If the position could not be determined.
     */
    public function tell();

    /**
     * Attempts to release a portable advisory lock.
     *
     * ```php
     * $file->unlock();
     * ```
     *
     * @throws LockException If the lock could not be released.
     */
    public function unlock();

    /**
     * Writes the contents of a string to the file.
     *
     * If the entirety of the contents of the string could not be written
     * to the file, an exception will be thrown. If only subset of the string
     * should be written, only that subset should be provided.
     *
     * ```php
     * $file->write('example');
     * ```
     *
     * @param string $contents The contents to write.
     *
     * @throws WriteException If the contents could not be written.
     */
    public function write($contents);
}
