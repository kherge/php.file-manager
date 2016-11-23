<?php

namespace KHerGe\File;

use KHerGe\File\Exception\PathException;

/**
 * Recursively removes a path from the file system.
 *
 * This function will delete a file path or recursively delete a directory path.
 * If the directory path is a symbolic link, it will not be followed by default
 * and the link itself will be deleted. If `$follow` is set to true, the link is
 * followed and the contents of the directory are also deleted.
 *
 * ```php
 * // Remove a directory path.
 * remove('/path/to/dir');
 *
 * // Remove a file path.
 * remove('/path/to/file');
 * ```
 *
 * @param string  $path   The path to remove.
 * @param boolean $follow Follow symlinks?
 *
 * @throws PathException If the path could not be removed.
 */
function remove($path, $follow = false)
{
    $link = is_link($path);

    if (is_dir($path) && (!$link || $follow)) {
        $dir = opendir($path);

        if (!$dir) {
            throw new PathException(
                'The directory "%s" could not be opened.',
                $path
            );
        }

        $last = null;

        while (false !== ($last = readdir($dir))) {
            if (('.' === $last) || ('..' === $last)) {
                continue;
            }

            $last = $path . DIRECTORY_SEPARATOR . $last;

            remove($last, $follow);
        }

        closedir($dir);

        if ($link) {
            remove($path);
        } elseif (!rmdir($path)) {
            throw new PathException(
                'The directory "%s" could not be removed.%s',
                $path,
                (null === $last)
                    ? ''
                    : sprintf(' The path "%s" was probably not deleted.', $last)
            );
        }
    } elseif (!unlink($path)) {
        throw new PathException(
            'The path "%s" could not be removed.',
            $path
        );
    }
}
