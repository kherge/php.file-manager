<?php

namespace KHerGe\File;

use KHerGe\File\Exception\ResourceException;

/**
 * Manages the file contents stored on the file system.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class File extends Stream
{
    /**
     * The file open mode.
     *
     * @var string
     */
    private $mode;

    /**
     * The path to the file.
     *
     * @var string
     */
    private $path;

    /**
     * Initializes the new file manager.
     *
     * @param string $path The path to the file.
     * @param string $mode The file open mode.
     *
     * @throws ResourceException If the file could not be opened.
     */
    public function __construct($path, $mode)
    {
        $stream = fopen($path, $mode);

        if (!$stream) {
            throw new ResourceException(
                "The file \"$path\" could not be opened ($mode)."
            );
        }

        parent::__construct($stream);

        $this->mode = $mode;
        $this->path = $path;
    }
}
