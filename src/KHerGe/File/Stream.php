<?php

namespace KHerGe\File;

use Generator;
use KHerGe\File\Exception\CursorException;
use KHerGe\File\Exception\LockException;
use KHerGe\File\Exception\ReadException;
use KHerGe\File\Exception\ResourceException;
use KHerGe\File\Exception\WriteException;

/**
 * Manages the file contents stored in a file stream.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Stream implements FileInterface
{
    /**
     * The file stream.
     *
     * @var resource
     */
    private $stream;

    /**
     * Initializes the new file stream manager.
     *
     * @param resource $stream The file stream.
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        if (null !== $this->stream) {
            $this->release();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        $content = fread($this->getStream(), 1);

        if (false === $content) {
            throw new CursorException(
                'The file could not be read to trigger an EOF check.'
            );
        }

        $eof = feof($this->getStream());

        if ('' !== $content) {
            $this->seek(-1, self::RELATIVE);
        }

        return $eof;
    }

    /**
     * {@inheritdoc}
     */
    public function iterate($bytes = 0, $buffer = 1024)
    {
        $generator = (0 === $bytes)
            ? $this->iterateAll($buffer)
            : $this->iterateBytes($bytes, $buffer);

        foreach ($generator as $yield) {
            yield $yield;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function lock($exclusive = false, $nonBlocking = false)
    {
        $this->checkLockSupport();

        $operations = $exclusive ? LOCK_EX : LOCK_SH;

        if ($nonBlocking) {
            $operations |= LOCK_NB;
        }

        if (!flock($this->stream, $operations)) {
            throw new LockException(
                'The file stream could not be locked.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($bytes = 0)
    {
        if (0 === $bytes) {
            return $this->readAll();
        }

        return $this->readBytes($bytes);
    }

    /**
     * {@inheritdoc}
     */
    public function release()
    {
        if (!fclose($this->getStream())) {
            throw new ResourceException(
                'The file stream could not be closed.'
            );
        }

        $this->stream = null;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position, $mode = self::EXACT)
    {
        if (-1 === fseek($this->getStream(), $position, $mode)) {
            throw new CursorException(
                'The internal cursor for the file stream could not be moved.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function size()
    {
        $this->seek(0);

        $count = 0;
        $stream = $this->getStream();

        do {
            $chunk = fread($stream, 8192);

            if (false === $chunk) {
                throw new ReadException('The file stream could not be read.');
            }

            $count += strlen($chunk);
        } while (!$this->eof());

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function stream(FileInterface $file, $bytes = 0, $buffer = 1024)
    {
        foreach ($file->iterate($bytes, $buffer) as $chunk) {
            $this->write($chunk);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        $position = ftell($this->getStream());

        if (false === $position) {
            throw new CursorException(
                'The internal cursor position for the file stream could not be determined.'
            );
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function unlock()
    {
        $this->checkLockSupport();

        if (!flock($this->stream, LOCK_UN)) {
            throw new LockException(
                'The file stream could not be unlocked.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($contents)
    {
        $wrote = fwrite($this->getStream(), $contents);

        if (false === $wrote) {
            throw new WriteException(
                'The string contents could not be written to the file stream.'
            );
        }

        if (($bytes = strlen($contents)) !== $wrote) {
            throw new WriteException(
                sprintf(
                    'Only %d of %d bytes could be written to the file stream.',
                    $wrote,
                    $bytes
                )
            );
        }
    }

    /**
     * Returns the file stream if it is available.
     *
     * @return resource The file stream.
     *
     * @throws ResourceException If the file stream is not available.
     */
    protected function getStream()
    {
        if (null === $this->stream) {
            throw new ResourceException(
                'The file stream is no longer available.'
            );
        }

        return $this->stream;
    }

    /**
     * Checks to see if the stream supports locking.
     *
     * @throws LockException If locking is not supported.
     */
    private function checkLockSupport()
    {
        if (!stream_supports_lock($this->stream)) {
            throw new LockException(
                'The file stream does not support locking.'
            );
        }
    }

    /**
     * Iterates through the file stream until the end.
     *
     * @param integer $buffer The number of bytes to yield.
     *
     * @return Generator|string[] The contents read from the file stream.
     *
     * @throws ReadException If the file stream could not be read.
     */
    private function iterateAll($buffer)
    {
        do {
            $chunk = fread($this->getStream(), $buffer);

            if (false === $chunk) {
                throw new ReadException(
                    'The file stream could not be iterated.'
                );
            }

            yield $chunk;
        } while (!$this->eof());
    }

    /**
     * Iterates a specific number of bytes from the file stream.
     *
     * @param integer $bytes  The number of bytes to read.
     * @param integer $buffer The number of bytes to yield.
     *
     * @return Generator|string[] The contents read from the file stream.
     *
     * @throws ReadException If the file stream could not be read.
     */
    private function iterateBytes($bytes, $buffer)
    {
        $total = $bytes;

        do {
            if ($buffer > $bytes) {
                $buffer = $bytes;
            }

            $chunk = fread($this->getStream(), $buffer);

            if (false === $chunk) {
                throw new ReadException(
                    'The file stream could not be iterated.'
                );
            }

            $bytes -= strlen($chunk);

            yield $chunk;
        } while (!$this->eof() && ($bytes > 0));

        if (0 !== $bytes) {
            throw new ReadException(
                sprintf(
                    'Only %d of %d bytes could be iterated through the file stream.',
                    $total - $bytes,
                    $total
                )
            );
        }
    }

    /**
     * Reads the file stream until the end.
     *
     * @return string The contents read from the file stream.
     *
     * @throws ReadException If the file stream could not be read.
     */
    private function readAll()
    {
        $buffer = '';
        $stream = $this->getStream();

        do {
            $chunk = fread($stream, 1024);

            if (false === $chunk) {
                throw new ReadException('The file stream could not be read.');
            }

            $buffer .= $chunk;
        } while (!$this->eof());

        return $buffer;
    }

    /**
     * Reads a specific number of bytes from the file stream.
     *
     * @param integer $bytes The number of bytes to read.
     *
     * @return string The contents read from the file stream.
     *
     * @throws ReadException If the file stream could not be read.
     */
    private function readBytes($bytes)
    {
        $buffer = fread($this->getStream(), $bytes);

        if (false === $buffer) {
            throw new ReadException(
                'The file stream could not be read.'
            );
        }

        if (($read = strlen($buffer)) !== $bytes) {
            throw new ReadException(
                sprintf(
                    'Only %d of %d bytes could be read from the file stream.',
                    $read,
                    $bytes
                )
            );
        }

        return $buffer;
    }
}
